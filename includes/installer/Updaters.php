<?php

/**
 * All DBs supported by MediaWiki need to implement this. Base interface for
 * Updaters, which is replacing updaters.inc
 */
interface Updaters {
	
	/**
	 * Get an array of updates to perform on the database. Should return a
	 * mutli-dimensional array. The main key is the MediaWiki version (1.12,
	 * 1.13...) with the values being arrays of updates, identical to how
	 * updaters.inc did it (for now)
	 *
	 * @return Array
	 */
	public function getUpdates();
	
}

/**
 * Mysql implementation.
 */
class MysqlUpdater implements Updaters {

	public function getUpdates() {
		return array(
			'1.2' => array(
				array( 'add_field', 'ipblocks',      'ipb_id',           'patch-ipblocks.sql' ),
				array( 'add_field', 'ipblocks',      'ipb_expiry',       'patch-ipb_expiry.sql' ),
				array( 'do_interwiki_update' ),
				array( 'do_index_update' ),
				array( 'add_table', 'hitcounter',                        'patch-hitcounter.sql' ),
				array( 'add_field', 'recentchanges', 'rc_type',          'patch-rc_type.sql' ),
			),
			'1.3' => array(
				array( 'add_field', 'user',          'user_real_name',   'patch-user-realname.sql' ),
				array( 'add_table', 'querycache',                        'patch-querycache.sql' ),
				array( 'add_table', 'objectcache',                       'patch-objectcache.sql' ),
				array( 'add_table', 'categorylinks',                     'patch-categorylinks.sql' ),
				array( 'do_old_links_update' ),
				array( 'fix_ancient_imagelinks' ),
				array( 'add_field', 'recentchanges', 'rc_ip',            'patch-rc_ip.sql' ),
			),
			'1.4' => array(
				array( 'do_image_name_unique_update' ),
				array( 'add_field', 'recentchanges', 'rc_id',            'patch-rc_id.sql' ),
				array( 'add_field', 'recentchanges', 'rc_patrolled',     'patch-rc-patrol.sql' ),
				array( 'add_table', 'logging',                           'patch-logging.sql' ),
				array( 'add_field', 'user',          'user_token',       'patch-user_token.sql' ),
				array( 'do_watchlist_update' ),
				array( 'do_user_update' ),
			),
			'1.5' => array(
				array( 'do_schema_restructuring' ),
				array( 'add_field', 'logging',       'log_params',       'patch-log_params.sql' ),
				array( 'check_bin', 'logging',       'log_title',        'patch-logging-title.sql', ),
				array( 'add_field', 'archive',       'ar_rev_id',        'patch-archive-rev_id.sql' ),
				array( 'add_field', 'page',          'page_len',         'patch-page_len.sql' ),
				array( 'do_inverse_timestamp' ),
				array( 'do_text_id' ),
				array( 'add_field', 'revision',      'rev_deleted',      'patch-rev_deleted.sql' ),
				array( 'add_field', 'image',         'img_width',        'patch-img_width.sql' ),
				array( 'add_field', 'image',         'img_metadata',     'patch-img_metadata.sql' ),
				array( 'add_field', 'user',          'user_email_token', 'patch-user_email_token.sql' ),
				array( 'add_field', 'archive',       'ar_text_id',       'patch-archive-text_id.sql' ),
				array( 'do_namespace_size' ),
				array( 'add_field', 'image',         'img_media_type',   'patch-img_media_type.sql' ),
				array( 'do_pagelinks_update' ),
				array( 'do_drop_img_type' ),
				array( 'do_user_unique_update' ),
				array( 'do_user_groups_update' ),
				array( 'add_field', 'site_stats',    'ss_total_pages',   'patch-ss_total_articles.sql' ),
				array( 'add_table', 'user_newtalk',                      'patch-usernewtalk2.sql' ),
				array( 'add_table', 'transcache',                        'patch-transcache.sql' ),
				array( 'add_field', 'interwiki',     'iw_trans',         'patch-interwiki-trans.sql' ),
				array( 'add_table', 'trackbacks',                        'patch-trackbacks.sql' ),
			),
			'1.6' => array(
				array( 'do_watchlist_null' ),
				array( 'do_logging_timestamp_index' ),
				array( 'add_field', 'ipblocks',        'ipb_range_start',  'patch-ipb_range_start.sql' ),
				array( 'do_page_random_update' ),
				array( 'add_field', 'user',            'user_registration', 'patch-user_registration.sql' ),
				array( 'do_templatelinks_update' ),
				array( 'add_table', 'externallinks',                       'patch-externallinks.sql' ),
				array( 'add_table', 'job',                                 'patch-job.sql' ),
				array( 'add_field', 'site_stats',      'ss_images',        'patch-ss_images.sql' ),
				array( 'add_table', 'langlinks',                           'patch-langlinks.sql' ),
				array( 'add_table', 'querycache_info',                     'patch-querycacheinfo.sql' ),
				array( 'add_table', 'filearchive',                         'patch-filearchive.sql' ),
				array( 'add_field', 'ipblocks',        'ipb_anon_only',    'patch-ipb_anon_only.sql' ),
				array( 'do_rc_indices_update' ),
			),
			'1.9' => array(
				array( 'add_field', 'user',          'user_newpass_time', 'patch-user_newpass_time.sql' ),
				array( 'add_table', 'redirect',                           'patch-redirect.sql' ),
				array( 'add_table', 'querycachetwo',                      'patch-querycachetwo.sql' ),
				array( 'add_field', 'ipblocks',      'ipb_enable_autoblock', 'patch-ipb_optional_autoblock.sql' ),
				array( 'do_backlinking_indices_update' ),
				array( 'add_field', 'recentchanges', 'rc_old_len',        'patch-rc_len.sql' ),
				array( 'add_field', 'user',          'user_editcount',    'patch-user_editcount.sql' ),
			),
			'1.10' => array(
				array( 'do_restrictions_update' ),
				array( 'add_field', 'logging',       'log_id',           'patch-log_id.sql' ),
				array( 'add_field', 'revision',      'rev_parent_id',    'patch-rev_parent_id.sql' ),
				array( 'add_field', 'page_restrictions', 'pr_id',        'patch-page_restrictions_sortkey.sql' ),
				array( 'add_field', 'revision',      'rev_len',          'patch-rev_len.sql' ),
				array( 'add_field', 'recentchanges', 'rc_deleted',       'patch-rc_deleted.sql' ),
				array( 'add_field', 'logging',       'log_deleted',      'patch-log_deleted.sql' ),
				array( 'add_field', 'archive',       'ar_deleted',       'patch-ar_deleted.sql' ),
				array( 'add_field', 'ipblocks',      'ipb_deleted',      'patch-ipb_deleted.sql' ),
				array( 'add_field', 'filearchive',   'fa_deleted',       'patch-fa_deleted.sql' ),
				array( 'add_field', 'archive',       'ar_len',           'patch-ar_len.sql' ),
			),
			'1.11' => array(
				array( 'add_field', 'ipblocks',      'ipb_block_email',  'patch-ipb_emailban.sql' ),
				array( 'do_categorylinks_indices_update' ),
				array( 'add_field', 'oldimage',      'oi_metadata',      'patch-oi_metadata.sql' ),
				array( 'do_archive_user_index' ),
				array( 'do_image_user_index' ),
				array( 'do_oldimage_user_index' ),
				array( 'add_field', 'archive',       'ar_page_id',       'patch-archive-page_id.sql' ),
				array( 'add_field', 'image',         'img_sha1',         'patch-img_sha1.sql' ),
			),
			'1.12' => array(
				array( 'add_table', 'protected_titles',                  'patch-protected_titles.sql' ),
			),
			'1.13' => array(
				array( 'add_field', 'ipblocks',      'ipb_by_text',      'patch-ipb_by_text.sql' ),
				array( 'add_table', 'page_props',                        'patch-page_props.sql' ),
				array( 'add_table', 'updatelog',                         'patch-updatelog.sql' ),
				array( 'add_table', 'category',                          'patch-category.sql' ),
				array( 'do_category_population' ),
				array( 'add_field', 'archive',       'ar_parent_id',     'patch-ar_parent_id.sql' ),
				array( 'add_field', 'user_newtalk',  'user_last_timestamp', 'patch-user_last_timestamp.sql' ),
				array( 'do_populate_parent_id' ),
				array( 'check_bin', 'protected_titles', 'pt_title',      'patch-pt_title-encoding.sql', ),
				array( 'maybe_do_profiling_memory_update' ),
				array( 'do_filearchive_indices_update' ),
			),
			'1.14' => array(
				array( 'add_field', 'site_stats',    'ss_active_users',  'patch-ss_active_users.sql' ),
				array( 'do_active_users_init' ),
				array( 'add_field', 'ipblocks',      'ipb_allow_usertalk', 'patch-ipb_allow_usertalk.sql' ),
			),
			'1.15' => array(
				array( 'do_unique_pl_tl_il' ),
				array( 'add_table', 'change_tag',                        'patch-change_tag.sql' ),
				array( 'add_table', 'tag_summary',                       'patch-change_tag.sql' ),
				array( 'add_table', 'valid_tag',                         'patch-change_tag.sql' ),
			),
			'1.16' => array(
				array( 'add_table', 'user_properties',                   'patch-user_properties.sql' ),
				array( 'add_table', 'log_search',                        'patch-log_search.sql' ),
				array( 'do_log_search_population' ),
				array( 'add_field', 'logging',       'log_user_text',    'patch-log_user_text.sql' ),
				array( 'add_table', 'l10n_cache',                        'patch-l10n_cache.sql' ),
				array( 'add_table', 'external_user',                     'patch-external_user.sql' ),
				array( 'add_index', 'log_search',    'ls_field_val',     'patch-log_search-rename-index.sql' ),
				array( 'add_index', 'change_tag',    'change_tag_rc_tag', 'patch-change_tag-indexes.sql' ),
				array( 'add_field', 'redirect',      'rd_interwiki',     'patch-rd_interwiki.sql' ),
				array( 'do_update_transcache_field' ),
				array( 'rename_eu_wiki_id' ),
				array( 'do_update_mime_minor_field' ),
				array( 'do_populate_rev_len' ),
			),
			'1.17' => array(
				array( 'add_table', 'iwlinks',                           'patch-iwlinks.sql' ),
				array( 'add_index', 'iwlinks', 'iwl_prefix_from_title',  'patch-rename-iwl_prefix.sql' ),
				array( 'add_field', 'updatelog', 'ul_value',              'patch-ul_value.sql' ),
				array( 'add_field', 'interwiki',     'iw_api',           'patch-iw_api_and_wikiid.sql' ),
			),
		);
	}
	
}

/**
 * Sqlite implementation.
 */
class SqliteUpdater implements Updaters {
	
	public function getUpdates() {
		return array(
			'1.14' => array(
				array( 'add_field', 'site_stats',    'ss_active_users',  'patch-ss_active_users.sql' ),
				array( 'do_active_users_init' ),
				array( 'add_field', 'ipblocks',      'ipb_allow_usertalk', 'patch-ipb_allow_usertalk.sql' ),
				array( 'sqlite_initial_indexes' ),
			),
			'1.15' => array(
				array( 'add_table', 'change_tag',                        'patch-change_tag.sql' ),
				array( 'add_table', 'tag_summary',                       'patch-change_tag.sql' ),
				array( 'add_table', 'valid_tag',                         'patch-change_tag.sql' ),
			),
			'1.16' => array(
				array( 'add_table', 'user_properties',                   'patch-user_properties.sql' ),
				array( 'add_table', 'log_search',                        'patch-log_search.sql' ),
				array( 'do_log_search_population' ),
				array( 'add_field', 'logging',       'log_user_text',    'patch-log_user_text.sql' ),
				array( 'add_table', 'l10n_cache',                        'patch-l10n_cache.sql' ),
				array( 'add_table', 'external_user',                     'patch-external_user.sql' ),
				array( 'add_index', 'log_search',    'ls_field_val',     'patch-log_search-rename-index.sql' ),
				array( 'add_index', 'change_tag',    'change_tag_rc_tag', 'patch-change_tag-indexes.sql' ),
				array( 'add_field', 'redirect',      'rd_interwiki',     'patch-rd_interwiki.sql' ),
				array( 'do_update_transcache_field' ),
				array( 'sqlite_setup_searchindex' ),
			),
			'1.17' => array(
				array( 'add_table', 'iwlinks',                            'patch-iwlinks.sql' ),
				array( 'add_index', 'iwlinks',   'iwl_prefix_from_title', 'patch-rename-iwl_prefix.sql' ),
				array( 'add_field', 'updatelog', 'ul_value',              'patch-ul_value.sql' ),
				array( 'add_field', 'interwiki',     'iw_api',           'patch-iw_api_and_wikiid.sql' ),
			),
		);
	}
	
}

/**
 * Oracle implementation.
 */
class OracleUpdater implements Updaters {
	
	public function getUpdates() {
		return array();
	}
	
}