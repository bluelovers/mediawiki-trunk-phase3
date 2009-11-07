/*
 * this file exposes some of the functionality of mwEmbed to wikis
 * that do not yet have js2 enabled
 */

var urlparts = getRemoteEmbedPath();
var mwEmbedHostPath = urlparts[0];
var reqAguments = urlparts[1];

addOnloadHook( function(){
	//only do rewrites if MV_EMBED / js2 is "off"
	if( typeof MV_EMBED_VERSION == 'undefined' ) {
		doPageSpecificRewrite();
	}
});

function doPageSpecificRewrite() {
	// Add media wizard
	if( wgAction == 'edit' || wgAction == 'submit' ) {
		load_mv_embed( function() {
			loadExternalJs( mwEmbedHostPath + '/editPage.js' + reqAguments );
		} );
	}
	
	//timed text dispaly:
	if(wgPageName.indexOf("TimedText") === 0){
		
	}
	
	// Firefogg integration
	if( wgPageName == "Special:Upload" ){		
		load_mv_embed( function() {
			loadExternalJs( mwEmbedHostPath + '/uploadPage.js' + reqAguments );
		} );
	}
	
	// Special api proxy page
	if( wgPageName == 'MediaWiki:ApiProxy' ){		
		var wgEnableIframeApiProxy = true;
		load_mv_embed( function() {
			js_log("Wiki:ApiProxy::");
			loadExternalJs( mwEmbedHostPath + '/apiProxyPage.js' + reqAguments );
		});
	}
	
	// OggHandler rewrite for view pages:
	var vidIdList = [];
	var divs = document.getElementsByTagName( 'div' );
	for( var i = 0; i < divs.length; i++ ) {
		if( divs[i].id && divs[i].id.substring( 0, 11 ) == 'ogg_player_' ) {
			vidIdList.push( divs[i].getAttribute( "id" ) );
		}
	}
	if( vidIdList.length > 0 ) {
		load_mv_embed( function() {
			mvJsLoader.embedVideoCheck( function() {
				// Do utility rewrite of OggHandler content:
				rewrite_for_OggHandler( vidIdList );
			} );
		} );
	}
}
// will be depreciated in favor of updates to OggHandler
function rewrite_for_OggHandler( vidIdList ){
	function procVidId( vidId ){
		js_log('vidIdList length: ' + vidIdList.length + ' left in the set: ' + vidIdList );
		
		// Grab the thumbnail and src of the video
		var pimg = $j( '#' + vidId + ' img' );
		var poster_attr = 'poster = "' + pimg.attr( 'src' ) + '" ';
		var pwidth = pimg.attr( 'width' );
		var pheight = pimg.attr( 'height' );

		var tag_type = 'video';
		
		// Check for audio
		if( pwidth == '22' && pheight == '22' ) {
			//set width to parent width:
			pwidth = $j( '#' + vidId ).width();
			pheight = '100';
			tag_type = 'audio';
			poster_attr = '';
		}

		// Parsed values:
		var src = '';
		var duration_attr = '';	
		var wikiTitleKey = $j( '#'+vidId + ' img').filter(':first').attr('alt').replace(/ /g, '_');
		var re = new RegExp( /videoUrl(&quot;:?\s*)*([^&]*)/ );
		src = re.exec( $j( '#'+vidId ).html() )[2];

		var re = new RegExp( /length(&quot;:?\s*)*([^,]*)/ );
		var dv = re.exec( $j( '#'+vidId ).html() )[2];
		if( dv ){
			duration_attr = 'durationHint="'+ dv +'" ';
		}

		var re = new RegExp( /offset(&quot;:?\s*)*([^&]*)/ );
		offset = re.exec( $j( '#'+vidId ).html() )[2];
		var offset_attr = offset ? 'startOffset="' + offset + '"' : '';

		if( src ) {
			var html_out = '';
			
			var common_attr = ' id="mwe_' + vidId +'" '+
					'wikiTitleKey="' + wikiTitleKey + '" ' +
					'src="' + src + '" ' +
					duration_attr +
					offset_attr + ' ';
					
			if( tag_type == 'audio' ){
				html_out='<audio' + common_attr + '></audio>'
			}else{
				html_out='<video' + common_attr +
				poster_attr + ' ' +
				'style="width:' + pwidth + 'px;height:' + pheight + 'px;">' +
				'</video>';
			}	
			//set the video tag inner html and update the height
			$j( '#' + vidId ).html( html_out )
				.css('height', pheight + 30);

		}				
		rewrite_by_id( 'mwe_' + vidId, function(){
			if(vidIdList.length != 0){
				alert('did first rewite now doing another');
				setTimeout( function(){
					procVidId( vidIdList.pop() )
				}, 1);
			}
		});
	};
	//process each item in the vidIdList (with setTimeout to avoid locking)
	setTimeout( function(){
		procVidId( vidIdList.pop() )
	}, 1);
}
function getRemoteEmbedPath() {
	for( var i = 0; i < document.getElementsByTagName( 'script' ).length; i++ ) {
		var s = document.getElementsByTagName( 'script' )[i];
		if( s.src.indexOf( '/remoteMwEmbed.js' ) != -1 ) {
			var reqStr = '';
			var scriptPath = '';
			if( s.src.indexOf( '?' ) != -1) {
				reqStr = s.src.substr( s.src.indexOf( '?' ) );
				scriptPath = s.src.substr( 0,  s.src.indexOf( '?' ) ).replace( '/remoteMwEmbed.js', '' );
			} else {
				scriptPath = s.src.replace( '/remoteMwEmbed.js', '' )
			}
			// Use the external_media_wizard path:
			return [scriptPath, reqStr];
		}
	}
}

function load_mv_embed( callback ) {	
	// Inject mv_embed if needed
	if( typeof $mw == 'undefined' ) {
		var mvurl = mwEmbedHostPath + '/mwEmbed/mv_embed.js' + reqAguments ;
		importScriptURI( mvurl );		
	}
	check_for_mv_embed( callback );
}

function check_for_mv_embed( callback ) {
	if( typeof $mw == 'undefined' ) {
		setTimeout( function(){
			check_for_mv_embed( callback );
		}, 25 );
	} else {
		callback();
	}
}
