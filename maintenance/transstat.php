<?php
/**
 * Statistics about the localisation.
 *
 * @package MediaWiki
 * @subpackage Maintenance
 *
 * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
 * @author Ashar Voultoiz <thoane@altern.org>
 *
 * Output is posted from time to time on:
 * http://meta.wikimedia.org/wiki/Localization_statistics
 */

require_once( 'commandLine.inc' );
require_once( 'languages.inc' );

if ( isset( $options['help'] ) ) {
	showUsage();
}
# Default output is WikiText
if ( !isset( $options['output'] ) ) {
	$options['output'] = 'wiki';
}

/** Print a usage message*/
function showUsage() {
	print <<<END
Usage: php transstat.php [--help] [--output=csv|text|wiki]
	--help : this helpful message
	--output : select an output engine one of:
		* 'csv'  : Comma Separated Values.
		* 'wiki' : MediaWiki syntax (default).
		* 'text' : Text with tabs.
Example: php maintenance/transstat.php --output=text

END;
	exit();
}

/** A general output object. Need to be overriden */
class statsOutput {
	function formatPercent( $subset, $total, $revert = false, $accuracy = 2 ) {
		return @sprintf( '%.' . $accuracy . 'f%%', 100 * $subset / $total );
	}

	# Override the following methods
	function heading() {
	}
	function footer() {
	}
	function blockstart() {
	}
	function blockend() {
	}
	function element( $in, $heading = false ) {
	}
}

/** Outputs WikiText */
class wikiStatsOutput extends statsOutput {
	function heading() {
		echo "'''Note:''' These statistics can be generated by running <code>php maintenance/transstat.php</code>.\n\n";
		echo "{| border=2 cellpadding=4 cellspacing=0 style=\"background: #f9f9f9; border: 1px #aaa solid; border-collapse: collapse;\" width=100%\n";
	}
	function footer() {
		echo "|}\n";
	}
	function blockstart() {
		echo "|-\n";
	}
	function blockend() {
		echo '';
	}
	function element( $in, $heading = false ) {
		echo ($heading ? '!' : '|') . " $in\n";
	}
	function formatPercent( $subset, $total, $revert = false, $accuracy = 2 ) {
		$v = @round(255 * $subset / $total);
		if ( $revert ) {
			$v = 255 - $v;
		}
		if ( $v < 128 ) {
			# Red to Yellow
			$red = 'FF';
			$green = sprintf( '%02X', 2 * $v );
		} else {
			# Yellow to Green
			$red = sprintf('%02X', 2 * ( 255 - $v ) );
			$green = 'FF';
		}
		$blue = '00';
		$color = $red . $green . $blue;

		$percent = statsOutput::formatPercent( $subset, $total, $revert, $accuracy );
		return 'bgcolor="#'. $color .'" | '. $percent;
	}
}

/** Output text. To be used on a terminal for example. */
class textStatsOutput extends statsOutput {
	function element( $in, $heading = false ) {
		echo $in."\t";
	}
	function blockend() {
		echo "\n";
	}
}

/** csv output. Some people love excel */
class csvStatsOutput extends statsOutput {
	function element( $in, $heading = false ) {
		echo $in . ";";
	}
	function blockend() {
		echo "\n";
	}
}

# Select an output engine
switch ( $options['output'] ) {
	case 'wiki':
		$wgOut = new wikiStatsOutput();
		break;
	case 'text':
		$wgOut = new textStatsOutput();
		break;
	case 'csv':
		$wgOut = new csvStatsOutput();
		break;
	default:
		showUsage();
}

# Languages
$wgLanguages = new languages();

# Header
$wgOut->heading();
$wgOut->blockstart();
$wgOut->element( 'Language', true );
$wgOut->element( 'Translated', true );
$wgOut->element( '%', true );
$wgOut->element( 'Possibly untranslated', true );
$wgOut->element( '%', true );
$wgOut->element( 'Obsolete', true );
$wgOut->element( '%', true );
$wgOut->blockend();

foreach ( $wgLanguages->getList() as $code ) {
	# Don't check English
	if ( $code == 'en' ) {
		continue;
	}
	
	# FIXME - temporary hack for this non-language won't appear
	if ( $code == 'enRTL' ) {
		continue;
	}

	# Calculate the numbers
	$name = $wgLang->getLanguageName( $code );
	$translatableMessagesNumber = count( $wgLanguages->getTranslatableMessages() );
	$localMessagesNumber = count( $wgLanguages->getMessagesFor( $code ) );
	$translatedMessagesNumber = count( $wgLanguages->getTranslatedMessages( $code ) );
	$translatedMessagesPercent = $wgOut->formatPercent( $translatedMessagesNumber, $translatableMessagesNumber );
	$duplicateMessagesNumber = count( $wgLanguages->getDuplicateMessages( $code ) );
	$duplicateMessagesPercent = $wgOut->formatPercent( $duplicateMessagesNumber, $translatedMessagesNumber, true );
	$obsoleteMessagesNumber = count( $wgLanguages->getObsoleteMessages( $code ) );
	$obsoleteMessagesPercent = $wgOut->formatPercent( $obsoleteMessagesNumber, $translatedMessagesNumber, true );

	# Output them
	$wgOut->blockstart();
	$wgOut->element( "$name ($code)" );
	$wgOut->element( "$translatedMessagesNumber/$translatableMessagesNumber" );
	$wgOut->element( $translatedMessagesPercent );
	$wgOut->element( "$duplicateMessagesNumber/$translatedMessagesNumber" );
	$wgOut->element( $duplicateMessagesPercent );
	$wgOut->element( "$obsoleteMessagesNumber/$translatedMessagesNumber" );
	$wgOut->element( $obsoleteMessagesPercent );
	$wgOut->blockend();
}

# Footer
$wgOut->footer();

?>
