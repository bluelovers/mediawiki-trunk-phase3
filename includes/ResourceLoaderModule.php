<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Trevor Parscal
 * @author Roan Kattouw
 */

defined( 'MEDIAWIKI' ) || die( 1 );

/**
 * Abstraction for resource loader modules, with name registration and maxage functionality.
 */
abstract class ResourceLoaderModule {
	
	/* Protected Members */

	protected $name = null;
	
	// In-object cache for file dependencies
	protected $fileDeps = array();
	// In-object cache for message blob mtime
	protected $msgBlobMtime = array();

	/* Methods */

	/**
	 * Get this module's name. This is set when the module is registered
	 * with ResourceLoader::register()
	 *
	 * @return Mixed: name (string) or null if no name was set
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set this module's name. This is called by ResourceLodaer::register()
	 * when registering the module. Other code should not call this.
	 *
	 * @param $name String: name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Get whether CSS for this module should be flipped
	 */
	public function getFlip( $context ) {
		return $context->getDirection() === 'rtl';
	}

	/**
	 * Get all JS for this module for a given language and skin.
	 * Includes all relevant JS except loader scripts.
	 *
	 * @param $context ResourceLoaderContext object
	 * @return String: JS
	 */
	public function getScript( ResourceLoaderContext $context ) {
		// Stub, override expected
		return '';
	}

	/**
	 * Get all CSS for this module for a given skin.
	 *
	 * @param $context ResourceLoaderContext object
	 * @return array: strings of CSS keyed by media type
	 */
	public function getStyles( ResourceLoaderContext $context ) {
		// Stub, override expected
		return '';
	}

	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		// Stub, override expected
		return array();
	}
	
	/**
	 * Get the group this module is in.
	 * 
	 * @return string of group name
	 */
	public function getGroup() {
		// Stub, override expected
		return null;
	}

	/**
	 * Get the loader JS for this module, if set.
	 *
	 * @return Mixed: loader JS (string) or false if no custom loader set
	 */
	public function getLoaderScript() {
		// Stub, override expected
		return false;
	}

	/**
	 * Get a list of modules this module depends on.
	 *
	 * Dependency information is taken into account when loading a module
	 * on the client side. When adding a module on the server side,
	 * dependency information is NOT taken into account and YOU are
	 * responsible for adding dependent modules as well. If you don't do
	 * this, the client side loader will send a second request back to the
	 * server to fetch the missing modules, which kind of defeats the
	 * purpose of the resource loader.
	 *
	 * To add dependencies dynamically on the client side, use a custom
	 * loader script, see getLoaderScript()
	 * @return Array of module names (strings)
	 */
	public function getDependencies() {
		// Stub, override expected
		return array();
	}
	
	/**
	 * Get the files this module depends on indirectly for a given skin.
	 * Currently these are only image files referenced by the module's CSS.
	 *
	 * @param $skin String: skin name
	 * @return array of files
	 */
	public function getFileDependencies( $skin ) {
		// Try in-object cache first
		if ( isset( $this->fileDeps[$skin] ) ) {
			return $this->fileDeps[$skin];
		}

		$dbr = wfGetDB( DB_SLAVE );
		$deps = $dbr->selectField( 'module_deps', 'md_deps', array(
				'md_module' => $this->getName(),
				'md_skin' => $skin,
			), __METHOD__
		);
		if ( !is_null( $deps ) ) {
			return $this->fileDeps[$skin] = (array) FormatJson::decode( $deps, true );
		}
		return $this->fileDeps[$skin] = array();
	}
	
	/**
	 * Set preloaded file dependency information. Used so we can load this
	 * information for all modules at once.
	 * @param $skin string Skin name
	 * @param $deps array Array of file names
	 */
	public function setFileDependencies( $skin, $deps ) {
		$this->fileDeps[$skin] = $deps;
	}
	
	/**
	 * Get the last modification timestamp of the message blob for this
	 * module in a given language.
	 * @param $lang string Language code
	 * @return int UNIX timestamp, or 0 if no blob found
	 */
	public function getMsgBlobMtime( $lang ) {
		if ( !count( $this->getMessages() ) )
			return 0;
		
		$dbr = wfGetDB( DB_SLAVE );
		$msgBlobMtime = $dbr->selectField( 'msg_resource', 'mr_timestamp', array(
				'mr_resource' => $this->getName(),
				'mr_lang' => $lang
			), __METHOD__
		);
		$this->msgBlobMtime[$lang] = $msgBlobMtime ? wfTimestamp( TS_UNIX, $msgBlobMtime ) : 0;
		return $this->msgBlobMtime[$lang];
	}
	
	/**
	 * Set a preloaded message blob last modification timestamp. Used so we
	 * can load this information for all modules at once.
	 * @param $lang string Language code
	 * @param $mtime int UNIX timestamp or 0 if there is no such blob
	 */
	public function setMsgBlobMtime( $lang, $mtime ) {
		$this->msgBlobMtime[$lang] = $mtime;
	}
	
	/* Abstract Methods */
	
	/**
	 * Get this module's last modification timestamp for a given
	 * combination of language, skin and debug mode flag. This is typically
	 * the highest of each of the relevant components' modification
	 * timestamps. Whenever anything happens that changes the module's
	 * contents for these parameters, the mtime should increase.
	 *
	 * @param $context ResourceLoaderContext object
	 * @return int UNIX timestamp
	 */
	public function getModifiedTime( ResourceLoaderContext $context ) {
		// 0 would mean now
		return 1;
	}
}

/**
 * Module based on local JS/CSS files. This is the most common type of module.
 */
class ResourceLoaderFileModule extends ResourceLoaderModule {
	/* Protected Members */

	protected $scripts = array();
	protected $styles = array();
	protected $messages = array();
	protected $group;
	protected $dependencies = array();
	protected $debugScripts = array();
	protected $languageScripts = array();
	protected $skinScripts = array();
	protected $skinStyles = array();
	protected $loaders = array();
	protected $parameters = array();

	// In-object cache for file dependencies
	protected $fileDeps = array();
	// In-object cache for mtime
	protected $modifiedTime = array();

	/* Methods */

	/**
	 * Construct a new module from an options array.
	 *
	 * @param $options array Options array. If empty, an empty module will be constructed
	 *
	 * $options format:
	 * 	array(
	 * 		// Required module options (mutually exclusive)
	 * 		'scripts' => 'dir/script.js' | array( 'dir/script1.js', 'dir/script2.js' ... ),
	 * 
	 * 		// Optional module options
	 * 		'languageScripts' => array(
	 * 			'[lang name]' => 'dir/lang.js' | '[lang name]' => array( 'dir/lang1.js', 'dir/lang2.js' ... )
	 * 			...
	 * 		),
	 * 		'skinScripts' => 'dir/skin.js' | array( 'dir/skin1.js', 'dir/skin2.js' ... ),
	 * 		'debugScripts' => 'dir/debug.js' | array( 'dir/debug1.js', 'dir/debug2.js' ... ),
	 *
	 * 		// Non-raw module options
	 * 		'dependencies' => 'module' | array( 'module1', 'module2' ... )
	 * 		'loaderScripts' => 'dir/loader.js' | array( 'dir/loader1.js', 'dir/loader2.js' ... ),
	 * 		'styles' => 'dir/file.css' | array( 'dir/file1.css', 'dir/file2.css' ... ), |
	 * 			array( 'dir/file1.css' => array( 'media' => 'print' ) ),
	 * 		'skinStyles' => array(
	 * 			'[skin name]' => 'dir/skin.css' |  array( 'dir/skin1.css', 'dir/skin2.css' ... ) |
	 * 				array( 'dir/file1.css' => array( 'media' => 'print' )
	 * 			...
	 * 		),
	 * 		'messages' => array( 'message1', 'message2' ... ),
	 * 		'group' => 'stuff',
	 * 	)
	 */
	public function __construct( $options = array(), $basePath = null ) {
		foreach ( $options as $option => $value ) {
			switch ( $option ) {
				case 'scripts':
				case 'debugScripts':
				case 'languageScripts':
				case 'skinScripts':
				case 'loaders':
					$this->{$option} = (array)$value;
					// Automatically prefix script paths
					if ( is_string( $basePath ) ) {
						foreach ( $this->{$option} as $key => $value ) {
							$this->{$option}[$key] = $basePath . $value;
						}
					}
					break;
				case 'styles':
				case 'skinStyles':
					$this->{$option} = (array)$value;
					// Automatically prefix style paths
					if ( is_string( $basePath ) ) {
						foreach ( $this->{$option} as $key => $value ) {
							if ( is_array( $value ) ) {
								$this->{$option}[$basePath . $key] = $value;
								unset( $this->{$option}[$key] );
							} else {
								$this->{$option}[$key] = $basePath . $value;
							}
						}
					}
					break;
				case 'dependencies':
				case 'messages':
					$this->{$option} = (array)$value;
					break;
				case 'group':
					$this->group = (string)$value;
					break;
			}
		}
	}
	
	/**
	 * Add script files to this module. In order to be valid, a module
	 * must contain at least one script file.
	 *
	 * @param $scripts Mixed: path to script file (string) or array of paths
	 */
	public function addScripts( $scripts ) {
		$this->scripts = array_merge( $this->scripts, (array)$scripts );
	}

	/**
	 * Add style (CSS) files to this module.
	 *
	 * @param $styles Mixed: path to CSS file (string) or array of paths
	 */
	public function addStyles( $styles ) {
		$this->styles = array_merge( $this->styles, (array)$styles );
	}

	/**
	 * Add messages to this module.
	 *
	 * @param $messages Mixed: message key (string) or array of message keys
	 */
	public function addMessages( $messages ) {
		$this->messages = array_merge( $this->messages, (array)$messages );
	}
	
	/**
	 * Sets the group of this module.
	 *
	 * @param $group string group name
	 */
	public function setGroup( $group ) {
		$this->group = $group;
	}
	
	/**
	 * Add dependencies. Dependency information is taken into account when
	 * loading a module on the client side. When adding a module on the
	 * server side, dependency information is NOT taken into account and
	 * YOU are responsible for adding dependent modules as well. If you
	 * don't do this, the client side loader will send a second request
	 * back to the server to fetch the missing modules, which kind of
	 * defeats the point of using the resource loader in the first place.
	 *
	 * To add dependencies dynamically on the client side, use a custom
	 * loader (see addLoaders())
	 *
	 * @param $dependencies Mixed: module name (string) or array of module names
	 */
	public function addDependencies( $dependencies ) {
		$this->dependencies = array_merge( $this->dependencies, (array)$dependencies );
	}

	/**
	 * Add debug scripts to the module. These scripts are only included
	 * in debug mode.
	 *
	 * @param $scripts Mixed: path to script file (string) or array of paths
	 */
	public function addDebugScripts( $scripts ) {
		$this->debugScripts = array_merge( $this->debugScripts, (array)$scripts );
	}

	/**
	 * Add language-specific scripts. These scripts are only included for
	 * a given language.
	 *
	 * @param $lang String: language code
	 * @param $scripts Mixed: path to script file (string) or array of paths
	 */
	public function addLanguageScripts( $lang, $scripts ) {
		$this->languageScripts = array_merge_recursive(
			$this->languageScripts,
			array( $lang => $scripts )
		);
	}

	/**
	 * Add skin-specific scripts. These scripts are only included for
	 * a given skin.
	 *
	 * @param $skin String: skin name, or 'default'
	 * @param $scripts Mixed: path to script file (string) or array of paths
	 */
	public function addSkinScripts( $skin, $scripts ) {
		$this->skinScripts = array_merge_recursive(
			$this->skinScripts,
			array( $skin => $scripts )
		);
	}

	/**
	 * Add skin-specific CSS. These CSS files are only included for a
	 * given skin. If there are no skin-specific CSS files for a skin,
	 * the files defined for 'default' will be used, if any.
	 *
	 * @param $skin String: skin name, or 'default'
	 * @param $scripts Mixed: path to CSS file (string) or array of paths
	 */
	public function addSkinStyles( $skin, $scripts ) {
		$this->skinStyles = array_merge_recursive(
			$this->skinStyles,
			array( $skin => $scripts )
		);
	}

	/**
	 * Add loader scripts. These scripts are loaded on every page and are
	 * responsible for registering this module using
	 * mediaWiki.loader.register(). If there are no loader scripts defined,
	 * the resource loader will register the module itself.
	 *
	 * Loader scripts are used to determine a module's dependencies
	 * dynamically on the client side (e.g. based on browser type/version).
	 * Note that loader scripts are included on every page, so they should
	 * be lightweight and use mediaWiki.loader.register()'s callback
	 * feature to defer dependency calculation.
	 *
	 * @param $scripts Mixed: path to script file (string) or array of paths
	 */
	public function addLoaders( $scripts ) {
		$this->loaders = array_merge( $this->loaders, (array)$scripts );
	}

	public function getScript( ResourceLoaderContext $context ) {
		$retval = $this->getPrimaryScript() . "\n" .
			$this->getLanguageScript( $context->getLanguage() ) . "\n" .
			$this->getSkinScript( $context->getSkin() );

		if ( $context->getDebug() ) {
			$retval .= $this->getDebugScript();
		}

		return $retval;
	}

	public function getStyles( ResourceLoaderContext $context ) {
		$styles = array();
		foreach ( $this->getPrimaryStyles() as $media => $style ) {
			if ( !isset( $styles[$media] ) ) {
				$styles[$media] = '';
			}
			$styles[$media] .= $style;
		}
		foreach ( $this->getSkinStyles( $context->getSkin() ) as $media => $style ) {
			if ( !isset( $styles[$media] ) ) {
				$styles[$media] = '';
			}
			$styles[$media] .= $style;
		}
		
		// Collect referenced files
		$files = array();
		foreach ( $styles as $media => $style ) {
			// Extract and store the list of referenced files
			$files = array_merge( $files, CSSMin::getLocalFileReferences( $style ) );
		}
		
		// Only store if modified
		if ( $files !== $this->getFileDependencies( $context->getSkin() ) ) {
			$encFiles = FormatJson::encode( $files );
			$dbw = wfGetDB( DB_MASTER );
			$dbw->replace( 'module_deps',
				array( array( 'md_module', 'md_skin' ) ), array(
					'md_module' => $this->getName(),
					'md_skin' => $context->getSkin(),
					'md_deps' => $encFiles,
				)
			);
		}
		
		return $styles;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function getGroup() {
		return $this->group;
	}

	public function getDependencies() {
		return $this->dependencies;
	}

	public function getLoaderScript() {
		if ( count( $this->loaders ) == 0 ) {
			return false;
		}

		return self::concatScripts( $this->loaders );
	}

	/**
	 * Get the last modified timestamp of this module, which is calculated
	 * as the highest last modified timestamp of its constituent files and
	 * the files it depends on (see getFileDependencies()). Only files
	 * relevant to the given language and skin are taken into account, and
	 * files only relevant in debug mode are not taken into account when
	 * debug mode is off.
	 *
	 * @param $context ResourceLoaderContext object
	 * @return Integer: UNIX timestamp
	 */
	public function getModifiedTime( ResourceLoaderContext $context ) {
		if ( isset( $this->modifiedTime[$context->getHash()] ) ) {
			return $this->modifiedTime[$context->getHash()];
		}
		wfProfileIn( __METHOD__ );
		
		// Sort of nasty way we can get a flat list of files depended on by all styles
		$styles = array();
		foreach ( self::organizeFilesByOption( $this->styles, 'media', 'all' ) as $media => $styleFiles ) {
			$styles = array_merge( $styles, $styleFiles );
		}
		$skinFiles = (array) self::getSkinFiles(
			$context->getSkin(), self::organizeFilesByOption( $this->skinStyles, 'media', 'all' )
		);
		foreach ( $skinFiles as $media => $styleFiles ) {
			$styles = array_merge( $styles, $styleFiles );
		}
		
		// Final merge, this should result in a master list of dependent files
		$files = array_merge(
			$this->scripts,
			$styles,
			$context->getDebug() ? $this->debugScripts : array(),
			isset( $this->languageScripts[$context->getLanguage()] ) ?
				(array) $this->languageScripts[$context->getLanguage()] : array(),
			(array) self::getSkinFiles( $context->getSkin(), $this->skinScripts ),
			$this->loaders,
			$this->getFileDependencies( $context->getSkin() )
		);
		
		wfProfileIn( __METHOD__.'-filemtime' );
		$filesMtime = max( array_map( 'filemtime', array_map( array( __CLASS__, 'remapFilename' ), $files ) ) );
		wfProfileOut( __METHOD__.'-filemtime' );
		$this->modifiedTime[$context->getHash()] = max( $filesMtime, $this->getMsgBlobMtime( $context->getLanguage() ) );
		wfProfileOut( __METHOD__ );
		return $this->modifiedTime[$context->getHash()];
	}

	/* Protected Members */

	/**
	 * Get the primary JS for this module. This is pulled from the
	 * script files added through addScripts()
	 *
	 * @return String: JS
	 */
	protected function getPrimaryScript() {
		return self::concatScripts( $this->scripts );
	}

	/**
	 * Get the primary CSS for this module. This is pulled from the CSS
	 * files added through addStyles()
	 *
	 * @return Array
	 */
	protected function getPrimaryStyles() {
		return self::concatStyles( $this->styles );
	}

	/**
	 * Get the debug JS for this module. This is pulled from the script
	 * files added through addDebugScripts()
	 *
	 * @return String: JS
	 */
	protected function getDebugScript() {
		return self::concatScripts( $this->debugScripts );
	}

	/**
	 * Get the language-specific JS for a given language. This is pulled
	 * from the language-specific script files added through addLanguageScripts()
	 *
	 * @return String: JS
	 */
	protected function getLanguageScript( $lang ) {
		if ( !isset( $this->languageScripts[$lang] ) ) {
			return '';
		}
		return self::concatScripts( $this->languageScripts[$lang] );
	}

	/**
	 * Get the skin-specific JS for a given skin. This is pulled from the
	 * skin-specific JS files added through addSkinScripts()
	 *
	 * @return String: JS
	 */
	protected function getSkinScript( $skin ) {
		return self::concatScripts( self::getSkinFiles( $skin, $this->skinScripts ) );
	}

	/**
	 * Get the skin-specific CSS for a given skin. This is pulled from the
	 * skin-specific CSS files added through addSkinStyles()
	 *
	 * @return Array: list of CSS strings keyed by media type
	 */
	protected function getSkinStyles( $skin ) {
		return self::concatStyles( self::getSkinFiles( $skin, $this->skinStyles ) );
	}

	/**
	 * Helper function to get skin-specific data from an array.
	 *
	 * @param $skin String: skin name
	 * @param $map Array: map of skin names to arrays
	 * @return $map[$skin] if set and non-empty, or $map['default'] if set, or an empty array
	 */
	protected static function getSkinFiles( $skin, $map ) {
		$retval = array();

		if ( isset( $map[$skin] ) && $map[$skin] ) {
			$retval = $map[$skin];
		} else if ( isset( $map['default'] ) ) {
			$retval = $map['default'];
		}

		return $retval;
	}

	/**
	 * Get the contents of a set of files and concatenate them, with
	 * newlines in between. Each file is used only once.
	 *
	 * @param $files Array of file names
	 * @return String: concatenated contents of $files
	 */
	protected static function concatScripts( $files ) {
		return implode( "\n", 
			array_map( 
				'file_get_contents', 
				array_map( 
					array( __CLASS__, 'remapFilename' ), 
					array_unique( (array) $files ) ) ) );
	}

	protected static function organizeFilesByOption( $files, $option, $default ) {
		$organizedFiles = array();
		foreach ( (array) $files as $key => $value ) {
			if ( is_int( $key ) ) {
				// File name as the value
				if ( !isset( $organizedFiles[$default] ) ) {
					$organizedFiles[$default] = array();
				}
				$organizedFiles[$default][] = $value;
			} else if ( is_array( $value ) ) {
				// File name as the key, options array as the value
				$media = isset( $value[$option] ) ? $value[$option] : $default;
				if ( !isset( $organizedFiles[$media] ) ) {
					$organizedFiles[$media] = array();
				}
				$organizedFiles[$media][] = $key;
			}
		}
		return $organizedFiles;
	}
	
	/**
	 * Get the contents of a set of CSS files, remap then and concatenate
	 * them, with newlines in between. Each file is used only once.
	 *
	 * @param $files Array of file names
	 * @return Array: list of concatenated and remapped contents of $files keyed by media type
	 */
	protected static function concatStyles( $styles ) {
		$styles = self::organizeFilesByOption( $styles, 'media', 'all' );
		foreach ( $styles as $media => $files ) {
			$styles[$media] =
				implode( "\n", 
					array_map( 
						array( __CLASS__, 'remapStyle' ), 
						array_unique( (array) $files ) ) );
		}
		return $styles;
	}

	/**
	 * Remap a relative to $IP. Used as a callback for array_map()
	 *
	 * @param $file String: file name
	 * @return string $IP/$file
	 */
	protected static function remapFilename( $file ) {
		global $IP;

		return "$IP/$file";
	}

	/**
	 * Get the contents of a CSS file and run it through CSSMin::remap().
	 * This wrapper is needed so we can use array_map() in concatStyles()
	 *
	 * @param $file String: file name
	 * @return string Remapped CSS
	 */
	protected static function remapStyle( $file ) {
		global $wgScriptPath;
		return CSSMin::remap(
			file_get_contents( self::remapFilename( $file ) ),
			dirname( $file ),
			$wgScriptPath . '/' . dirname( $file ),
			true
		);
	}
}

/**
 * Abstraction for resource loader modules which pull from wiki pages
 * 
 * This can only be used for wiki pages in the MediaWiki and User namespaces, because of it's dependence on the
 * functionality of Title::isValidCssJsSubpage.
 */
abstract class ResourceLoaderWikiModule extends ResourceLoaderModule {
	
	/* Protected Members */
	
	// In-object cache for modified time
	protected $modifiedTime = array();
	
	/* Abstract Protected Methods */
	
	abstract protected function getPages( ResourceLoaderContext $context );
	
	/* Protected Methods */
	
	protected function getContent( $page, $ns ) {
		if ( $ns === NS_MEDIAWIKI ) {
			return wfMsgExt( $page, 'content' );
		}
		if ( $title = Title::newFromText( $page, $ns ) ) {
			if ( $title->isValidCssJsSubpage() && $revision = Revision::newFromTitle( $title ) ) {
				return $revision->getRawText();
			}
		}
		return null;
	}
	
	/* Methods */

	public function getScript( ResourceLoaderContext $context ) {
		$scripts = '';
		foreach ( $this->getPages( $context ) as $page => $options ) {
			if ( $options['type'] === 'script' ) {
				if ( $script = $this->getContent( $page, $options['ns'] ) ) {
					$ns = MWNamespace::getCanonicalName( $options['ns'] );
					$scripts .= "/*$ns:$page */\n$script\n";
				}
			}
		}
		return $scripts;
	}

	public function getStyles( ResourceLoaderContext $context ) {
		
		$styles = array();
		foreach ( $this->getPages( $context ) as $page => $options ) {
			if ( $options['type'] === 'style' ) {
				$media = isset( $options['media'] ) ? $options['media'] : 'all';
				if ( $style = $this->getContent( $page, $options['ns'] ) ) {
					if ( !isset( $styles[$media] ) ) {
						$styles[$media] = '';
					}
					$ns = MWNamespace::getCanonicalName( $options['ns'] );
					$styles[$media] .= "/* $ns:$page */\n$style\n";
				}
			}
		}
		return $styles;
	}

	public function getModifiedTime( ResourceLoaderContext $context ) {
		$hash = $context->getHash();
		if ( isset( $this->modifiedTime[$hash] ) ) {
			return $this->modifiedTime[$hash];
		}

		$titles = array();
		foreach ( $this->getPages( $context ) as $page => $options ) {
			$titles[$options['ns']][$page] = true;
		}

		$modifiedTime = 1; // wfTimestamp() interprets 0 as "now"

		if ( $titles ) {
			$dbr = wfGetDB( DB_SLAVE );
			$latest = $dbr->selectField( 'page', 'MAX(page_touched)',
				$dbr->makeWhereFrom2d( $titles, 'page_namespace', 'page_title' ),
				__METHOD__ );

			if ( $latest ) {
				$modifiedTime = wfTimestamp( TS_UNIX, $latest );
			}
		}

		return $this->modifiedTime[$hash] = $modifiedTime;
	}
}

/**
 * Module for site customizations
 */
class ResourceLoaderSiteModule extends ResourceLoaderWikiModule {

	/* Protected Methods */

	protected function getPages( ResourceLoaderContext $context ) {
		global $wgHandheldStyle;
		
		$pages = array(
			'Common.js' => array( 'ns' => NS_MEDIAWIKI, 'type' => 'script' ),
			'Common.css' => array( 'ns' => NS_MEDIAWIKI, 'type' => 'style' ),
			ucfirst( $context->getSkin() ) . '.js' => array( 'ns' => NS_MEDIAWIKI, 'type' => 'script' ),
			ucfirst( $context->getSkin() ) . '.css' => array( 'ns' => NS_MEDIAWIKI, 'type' => 'style' ),
			'Print.css' => array( 'ns' => NS_MEDIAWIKI, 'type' => 'style', 'media' => 'print' ),
		);
		if ( $wgHandheldStyle ) {
			$pages['Handheld.css'] = array( 'ns' => NS_MEDIAWIKI, 'type' => 'style', 'media' => 'handheld' );
		}
		return $pages;
	}
	
	/* Methods */
	
	public function getGroup() {
		return 'site';
	}
}

/**
 * Module for user customizations
 */
class ResourceLoaderUserModule extends ResourceLoaderWikiModule {

	/* Protected Methods */

	protected function getPages( ResourceLoaderContext $context ) {
		global $wgAllowUserCss;
		
		if ( $context->getUser() && $wgAllowUserCss ) {
			$username = $context->getUser();
			return array(
				"$username/common.js" => array( 'ns' => NS_USER, 'type' => 'script' ),
				"$username/" . $context->getSkin() . '.js' => array( 'ns' => NS_USER, 'type' => 'script' ),
				"$username/common.css" => array( 'ns' => NS_USER, 'type' => 'style' ),
				"$username/" . $context->getSkin() . '.css' => array( 'ns' => NS_USER, 'type' => 'style' ),
			);
		}
		return array();
	}
	
	/* Methods */
	
	public function getGroup() {
		return 'user';
	}
}

/**
 * Module for user preference customizations
 */
class ResourceLoaderUserOptionsModule extends ResourceLoaderModule {

	/* Protected Members */

	protected $modifiedTime = array();

	/* Methods */

	public function getModifiedTime( ResourceLoaderContext $context ) {
		$hash = $context->getHash();
		if ( isset( $this->modifiedTime[$hash] ) ) {
			return $this->modifiedTime[$hash];
		}

		global $wgUser;

		if ( $context->getUser() === $wgUser->getName() ) {
			return $this->modifiedTime[$hash] = $wgUser->getTouched();
		} else {
			return 1;
		}
	}

	/**
	 * Fetch the context's user options, or if it doesn't match current user,
	 * the default options.
	 * 
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	protected function contextUserOptions( ResourceLoaderContext $context ) {
		global $wgUser;

		// Verify identity -- this is a private module
		if ( $context->getUser() === $wgUser->getName() ) {
			return $wgUser->getOptions();
		} else {
			return User::getDefaultOptions();
		}
	}

	public function getScript( ResourceLoaderContext $context ) {
		$encOptions = FormatJson::encode( $this->contextUserOptions( $context ) );
		return "mediaWiki.user.options.set( $encOptions );";
	}

	public function getStyles( ResourceLoaderContext $context ) {
		global $wgAllowUserCssPrefs;

		if ( $wgAllowUserCssPrefs ) {
			$options = $this->contextUserOptions( $context );

			// Build CSS rules
			$rules = array();
			if ( $options['underline'] < 2 ) {
				$rules[] = "a { text-decoration: " . ( $options['underline'] ? 'underline' : 'none' ) . "; }";
			}
			if ( $options['highlightbroken'] ) {
				$rules[] = "a.new, #quickbar a.new { color: #ba0000; }\n";
			} else {
				$rules[] = "a.new, #quickbar a.new, a.stub, #quickbar a.stub { color: inherit; }";
				$rules[] = "a.new:after, #quickbar a.new:after { content: '?'; color: #ba0000; }";
				$rules[] = "a.stub:after, #quickbar a.stub:after { content: '!'; color: #772233; }";
			}
			if ( $options['justify'] ) {
				$rules[] = "#article, #bodyContent, #mw_content { text-align: justify; }\n";
			}
			if ( !$options['showtoc'] ) {
				$rules[] = "#toc { display: none; }\n";
			}
			if ( !$options['editsection'] ) {
				$rules[] = ".editsection { display: none; }\n";
			}
			if ( $options['editfont'] !== 'default' ) {
				$rules[] = "textarea { font-family: {$options['editfont']}; }\n";
			}
			return array( 'all' => implode( "\n", $rules ) );
		}
		return array();
	}

	public function getFlip( $context ) {
		global $wgContLang;

		return $wgContLang->getDir() !== $context->getDirection();
	}

	public function getGroup() {
		return 'private';
	}
}

class ResourceLoaderStartUpModule extends ResourceLoaderModule {
	/* Protected Members */

	protected $modifiedTime = array();

	/* Protected Methods */
	
	protected function getConfig( $context ) {
		global $wgLoadScript, $wgScript, $wgStylePath, $wgScriptExtension, 
			$wgArticlePath, $wgScriptPath, $wgServer, $wgContLang, $wgBreakFrames, 
			$wgVariantArticlePath, $wgActionPaths, $wgUseAjax, $wgVersion, 
			$wgEnableAPI, $wgEnableWriteAPI, $wgDBname, $wgEnableMWSuggest, 
			$wgSitename, $wgFileExtensions;

		// Pre-process information
		$separatorTransTable = $wgContLang->separatorTransformTable();
		$separatorTransTable = $separatorTransTable ? $separatorTransTable : array();
		$compactSeparatorTransTable = array(
			implode( "\t", array_keys( $separatorTransTable ) ),
			implode( "\t", $separatorTransTable ),
		);
		$digitTransTable = $wgContLang->digitTransformTable();
		$digitTransTable = $digitTransTable ? $digitTransTable : array();
		$compactDigitTransTable = array(
			implode( "\t", array_keys( $digitTransTable ) ),
			implode( "\t", $digitTransTable ),
		);
		$mainPage = Title::newMainPage();
		
		// Build list of variables
		$vars = array(
			'wgLoadScript' => $wgLoadScript,
			'debug' => $context->getDebug(),
			'skin' => $context->getSkin(),
			'stylepath' => $wgStylePath,
			'wgUrlProtocols' => wfUrlProtocols(),
			'wgArticlePath' => $wgArticlePath,
			'wgScriptPath' => $wgScriptPath,
			'wgScriptExtension' => $wgScriptExtension,
			'wgScript' => $wgScript,
			'wgVariantArticlePath' => $wgVariantArticlePath,
			'wgActionPaths' => $wgActionPaths,
			'wgServer' => $wgServer,
			'wgUserLanguage' => $context->getLanguage(),
			'wgContentLanguage' => $wgContLang->getCode(),
			'wgBreakFrames' => $wgBreakFrames,
			'wgVersion' => $wgVersion,
			'wgEnableAPI' => $wgEnableAPI,
			'wgEnableWriteAPI' => $wgEnableWriteAPI,
			'wgSeparatorTransformTable' => $compactSeparatorTransTable,
			'wgDigitTransformTable' => $compactDigitTransTable,
			'wgMainPageTitle' => $mainPage ? $mainPage->getPrefixedText() : null,
			'wgFormattedNamespaces' => $wgContLang->getFormattedNamespaces(),
			'wgNamespaceIds' => $wgContLang->getNamespaceIds(),
			'wgSiteName' => $wgSitename,
			'wgFileExtensions' => $wgFileExtensions,
			'wgDBname' => $wgDBname,
		);
		if ( $wgContLang->hasVariants() ) {
			$vars['wgUserVariant'] = $wgContLang->getPreferredVariant();
		}
		if ( $wgUseAjax && $wgEnableMWSuggest ) {
			$vars['wgMWSuggestTemplate'] = SearchEngine::getMWSuggestTemplate();
		}
		
		return $vars;
	}
	
	/**
	 * Gets registration code for all modules
	 *
	 * @param $context ResourceLoaderContext object
	 * @return String: JavaScript code for registering all modules with the client loader
	 */
	public static function getModuleRegistrations( ResourceLoaderContext $context ) {
		global $wgCacheEpoch;
		wfProfileIn( __METHOD__ );
		
		$out = '';
		$registrations = array();
		foreach ( $context->getResourceLoader()->getModules() as $name => $module ) {
			// Support module loader scripts
			if ( ( $loader = $module->getLoaderScript() ) !== false ) {
				$deps = $module->getDependencies();
				$group = $module->getGroup();
				$version = wfTimestamp( TS_ISO_8601_BASIC, round( $module->getModifiedTime( $context ), -2 ) );
				$out .= ResourceLoader::makeCustomLoaderScript( $name, $version, $deps, $group, $loader );
			}
			// Automatically register module
			else {
				$mtime = max( $module->getModifiedTime( $context ), wfTimestamp( TS_UNIX, $wgCacheEpoch ) );
				// Modules without dependencies or a group pass two arguments (name, timestamp) to 
				// mediaWiki.loader.register()
				if ( !count( $module->getDependencies() && $module->getGroup() === null ) ) {
					$registrations[] = array( $name, $mtime );
				}
				// Modules with dependencies but no group pass three arguments (name, timestamp, dependencies) 
				// to mediaWiki.loader.register()
				else if ( $module->getGroup() === null ) {
					$registrations[] = array(
						$name, $mtime,  $module->getDependencies() );
				}
				// Modules with dependencies pass four arguments (name, timestamp, dependencies, group) 
				// to mediaWiki.loader.register()
				else {
					$registrations[] = array(
						$name, $mtime,  $module->getDependencies(), $module->getGroup() );
				}
			}
		}
		$out .= ResourceLoader::makeLoaderRegisterScript( $registrations );
		
		wfProfileOut( __METHOD__ );
		return $out;
	}

	/* Methods */

	public function getScript( ResourceLoaderContext $context ) {
		global $IP, $wgLoadScript;

		$out = file_get_contents( "$IP/resources/startup.js" );
		if ( $context->getOnly() === 'scripts' ) {
			// Build load query for jquery and mediawiki modules
			$query = array(
				'modules' => implode( '|', array( 'jquery', 'mediawiki' ) ),
				'only' => 'scripts',
				'lang' => $context->getLanguage(),
				'skin' => $context->getSkin(),
				'debug' => $context->getDebug() ? 'true' : 'false',
				'version' => wfTimestamp( TS_ISO_8601_BASIC, round( max(
					$context->getResourceLoader()->getModule( 'jquery' )->getModifiedTime( $context ),
					$context->getResourceLoader()->getModule( 'mediawiki' )->getModifiedTime( $context )
				), -2 ) )
			);
			// Ensure uniform query order
			ksort( $query );
			
			// Startup function
			$configuration = FormatJson::encode( $this->getConfig( $context ) );
			$registrations = self::getModuleRegistrations( $context );
			$out .= "var startUp = function() {\n\t$registrations\n\tmediaWiki.config.set( $configuration );\n};";
			
			// Conditional script injection
			$scriptTag = Xml::escapeJsString( Html::linkedScript( $wgLoadScript . '?' . wfArrayToCGI( $query ) ) );
			$out .= "if ( isCompatible() ) {\n\tdocument.write( '$scriptTag' );\n}\ndelete isCompatible;";
		}

		return $out;
	}

	public function getModifiedTime( ResourceLoaderContext $context ) {
		global $IP, $wgCacheEpoch;

		$hash = $context->getHash();
		if ( isset( $this->modifiedTime[$hash] ) ) {
			return $this->modifiedTime[$hash];
		}
		$this->modifiedTime[$hash] = filemtime( "$IP/resources/startup.js" );

		// ATTENTION!: Because of the line above, this is not going to cause infinite recursion - think carefully
		// before making changes to this code!
		$time = wfTimestamp( TS_UNIX, $wgCacheEpoch );
		foreach ( $context->getResourceLoader()->getModules() as $module ) {
			$time = max( $time, $module->getModifiedTime( $context ) );
		}
		return $this->modifiedTime[$hash] = $time;
	}

	public function getFlip( $context ) {
		global $wgContLang;

		return $wgContLang->getDir() !== $context->getDirection();
	}
	
	/* Methods */
	
	public function getGroup() {
		return 'startup';
	}
}
