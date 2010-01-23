<?php

/*
 * Created on Sep 7, 2006
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2006 Yuri Astrakhan <Firstname><Lastname>@gmail.com
 *
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
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	// Eclipse helper - will be ignored in production
	require_once ( 'ApiBase.php' );
}

/**
 * This is a base class for all Query modules.
 * It provides some common functionality such as constructing various SQL
 * queries.
 *
 * @ingroup API
 */
abstract class ApiQueryBase extends ApiBase {

	private $mQueryModule, $mDb, $tables, $where, $fields, $options, $join_conds;

	public function __construct( $query, $moduleName, $paramPrefix = '' ) {
		parent :: __construct( $query->getMain(), $moduleName, $paramPrefix );
		$this->mQueryModule = $query;
		$this->mDb = null;
		$this->resetQueryParams();
	}

	/**
	 * Blank the internal arrays with query parameters
	 */
	protected function resetQueryParams() {
		$this->tables = array ();
		$this->where = array ();
		$this->fields = array ();
		$this->options = array ();
		$this->join_conds = array ();
	}

	/**
	 * Add a set of tables to the internal array
	 * @param $tables mixed Table name or array of table names
	 * @param $alias mixed Table alias, or null for no alias. Cannot be
	 *  used with multiple tables
	 */
	protected function addTables( $tables, $alias = null ) {
		if ( is_array( $tables ) ) {
			if ( !is_null( $alias ) )
				ApiBase :: dieDebug( __METHOD__, 'Multiple table aliases not supported' );
			$this->tables = array_merge( $this->tables, $tables );
		} else {
			if ( !is_null( $alias ) )
				$tables = $this->getAliasedName( $tables, $alias );
			$this->tables[] = $tables;
		}
	}
	
	/**
	 * Get the SQL for a table name with alias
	 * @param $table string Table name
	 * @param $alias string Alias
	 * @return string SQL
	 */
	protected function getAliasedName( $table, $alias ) {
		return $this->getDB()->tableName( $table ) . ' ' . $alias;
	}
	
	/**
	 * Add a set of JOIN conditions to the internal array
	 *
	 * JOIN conditions are formatted as array( tablename => array(jointype,
	 * conditions) e.g. array('page' => array('LEFT JOIN',
	 * 'page_id=rev_page')) . conditions may be a string or an
	 * addWhere()-style array
	 * @param $join_conds array JOIN conditions
	 */
	protected function addJoinConds( $join_conds ) {
		if ( !is_array( $join_conds ) )
			ApiBase::dieDebug( __METHOD__, 'Join conditions have to be arrays' );
		$this->join_conds = array_merge( $this->join_conds, $join_conds );
	}

	/**
	 * Add a set of fields to select to the internal array
	 * @param $value mixed Field name or array of field names
	 */
	protected function addFields( $value ) {
		if ( is_array( $value ) )
			$this->fields = array_merge( $this->fields, $value );
		else
			$this->fields[] = $value;
	}

	/**
	 * Same as addFields(), but add the fields only if a condition is met
	 * @param $value mixed See addFields()
	 * @param $condition bool If false, do nothing
	 * @return bool $condition
	 */
	protected function addFieldsIf( $value, $condition ) {
		if ( $condition ) {
			$this->addFields( $value );
			return true;
		}
		return false;
	}

	/**
	 * Add a set of WHERE clauses to the internal array.
	 * Clauses can be formatted as 'foo=bar' or array('foo' => 'bar'),
	 * the latter only works if the value is a constant (i.e. not another field)
	 *
	 * If $value is an empty array, this function does nothing.
	 *
	 * For example, array('foo=bar', 'baz' => 3, 'bla' => 'foo') translates
	 * to "foo=bar AND baz='3' AND bla='foo'"
	 * @param $value mixed String or array
	 */
	protected function addWhere( $value ) {
		if ( is_array( $value ) ) {
			// Sanity check: don't insert empty arrays,
			// Database::makeList() chokes on them
			if ( count( $value ) )
				$this->where = array_merge( $this->where, $value );
		}
		else
			$this->where[] = $value;
	}

	/**
	 * Same as addWhere(), but add the WHERE clauses only if a condition is met
	 * @param $value mixed See addWhere()
	 * @param $condition boolIf false, do nothing
	 * @return bool $condition
	 */
	protected function addWhereIf( $value, $condition ) {
		if ( $condition ) {
			$this->addWhere( $value );
			return true;
		}
		return false;
	}

	/**
	 * Equivalent to addWhere(array($field => $value))
	 * @param $field string Field name
	 * @param $value string Value; ignored if null or empty array;
	 */
	protected function addWhereFld( $field, $value ) {
		// Use count() to its full documented capabilities to simultaneously 
		// test for null, empty array or empty countable object
		if ( count( $value ) )
			$this->where[$field] = $value;
	}

	/**
	 * Add a WHERE clause corresponding to a range, and an ORDER BY
	 * clause to sort in the right direction
	 * @param $field string Field name
	 * @param $dir string If 'newer', sort in ascending order, otherwise
	 *  sort in descending order
	 * @param $start string Value to start the list at. If $dir == 'newer'
	 *  this is the lower boundary, otherwise it's the upper boundary
	 * @param $end string Value to end the list at. If $dir == 'newer' this
	 *  is the upper boundary, otherwise it's the lower boundary
	 * @param $sort bool If false, don't add an ORDER BY clause
	 */
	protected function addWhereRange( $field, $dir, $start, $end, $sort = true ) {
		$isDirNewer = ( $dir === 'newer' );
		$after = ( $isDirNewer ? '>=' : '<=' );
		$before = ( $isDirNewer ? '<=' : '>=' );
		$db = $this->getDB();

		if ( !is_null( $start ) )
			$this->addWhere( $field . $after . $db->addQuotes( $start ) );

		if ( !is_null( $end ) )
			$this->addWhere( $field . $before . $db->addQuotes( $end ) );

		if ( $sort ) {
			$order = $field . ( $isDirNewer ? '' : ' DESC' );
			if ( !isset( $this->options['ORDER BY'] ) )
				$this->addOption( 'ORDER BY', $order );
			else
				$this->addOption( 'ORDER BY', $this->options['ORDER BY'] . ', ' . $order );
		}
	}

	/**
	 * Add an option such as LIMIT or USE INDEX. If an option was set
	 * before, the old value will be overwritten
	 * @param $name string Option name
	 * @param $value string Option value
	 */
	protected function addOption( $name, $value = null ) {
		if ( is_null( $value ) )
			$this->options[] = $name;
		else
			$this->options[$name] = $value;
	}

	/**
	 * Execute a SELECT query based on the values in the internal arrays
	 * @param $method string Function the query should be attributed to.
	 *  You should usually use __METHOD__ here
	 * @return ResultWrapper
	 */
	protected function select( $method ) {
		// getDB has its own profileDBIn/Out calls
		$db = $this->getDB();

		$this->profileDBIn();
		$res = $db->select( $this->tables, $this->fields, $this->where, $method, $this->options, $this->join_conds );
		$this->profileDBOut();

		return $res;
	}

	/**
	 * Estimate the row count for the SELECT query that would be run if we
	 * called select() right now, and check if it's acceptable.
	 * @return bool true if acceptable, false otherwise
	 */
	protected function checkRowCount() {
		$db = $this->getDB();
		$this->profileDBIn();
		$rowcount = $db->estimateRowCount( $this->tables, $this->fields, $this->where, __METHOD__, $this->options );
		$this->profileDBOut();

		global $wgAPIMaxDBRows;
		if ( $rowcount > $wgAPIMaxDBRows )
			return false;
		return true;
	}

	/**
	 * Add information (title and namespace) about a Title object to a
	 * result array
	 * @param $arr array Result array à la ApiResult
	 * @param $title Title
	 * @param $prefix string Module prefix
	 */
	public static function addTitleInfo( &$arr, $title, $prefix = '' ) {
		$arr[$prefix . 'ns'] = intval( $title->getNamespace() );
		$arr[$prefix . 'title'] = $title->getPrefixedText();
	}

	/**
	 * Override this method to request extra fields from the pageSet
	 * using $pageSet->requestField('fieldName')
	 * @param $pageSet ApiPageSet
	 */
	public function requestExtraData( $pageSet ) {
	}

	/**
	 * Get the main Query module
	 * @return ApiQuery
	 */
	public function getQuery() {
		return $this->mQueryModule;
	}

	/**
	 * Add a sub-element under the page element with the given page ID
	 * @param $pageId int Page ID
	 * @param $data array Data array à la ApiResult 
	 * @return bool Whether the element fit in the result
	 */
	protected function addPageSubItems( $pageId, $data ) {
		$result = $this->getResult();
		$result->setIndexedTagName( $data, $this->getModulePrefix() );
		return $result->addValue( array( 'query', 'pages', intval( $pageId ) ),
			$this->getModuleName(),
			$data );
	}
	
	/**
	 * Same as addPageSubItems(), but one element of $data at a time
	 * @param $pageId int Page ID
	 * @param $data array Data array à la ApiResult
	 * @param $elemname string XML element name. If null, getModuleName()
	 *  is used
	 * @return bool Whether the element fit in the result
	 */
	protected function addPageSubItem( $pageId, $item, $elemname = null ) {
		if ( is_null( $elemname ) )
			$elemname = $this->getModulePrefix();
		$result = $this->getResult();
		$fit = $result->addValue( array( 'query', 'pages', $pageId,
					 $this->getModuleName() ), null, $item );
		if ( !$fit )
			return false;
		$result->setIndexedTagName_internal( array( 'query', 'pages', $pageId,
				$this->getModuleName() ), $elemname );
		return true;
	}

	/**
	 * Set a query-continue value
	 * @param $paramName string Parameter name
	 * @param $paramValue string Parameter value
	 */
	protected function setContinueEnumParameter( $paramName, $paramValue ) {
		$paramName = $this->encodeParamName( $paramName );
		$msg = array( $paramName => $paramValue );
		$this->getResult()->disableSizeCheck();
		$this->getResult()->addValue( 'query-continue', $this->getModuleName(), $msg );
		$this->getResult()->enableSizeCheck();
	}

	/**
	 * Get the Query database connection (read-only)
	 * @return Database
	 */
	protected function getDB() {
		if ( is_null( $this->mDb ) )
			$this->mDb = $this->getQuery()->getDB();
		return $this->mDb;
	}

	/**
	 * Selects the query database connection with the given name.
	 * See ApiQuery::getNamedDB() for more information
	 * @param $name string Name to assign to the database connection
	 * @param $db int One of the DB_* constants
	 * @param $groups array Query groups
	 * @return Database 
	 */
	public function selectNamedDB( $name, $db, $groups ) {
		$this->mDb = $this->getQuery()->getNamedDB( $name, $db, $groups );
	}

	/**
	 * Get the PageSet object to work on
	 * @return ApiPageSet
	 */
	protected function getPageSet() {
		return $this->getQuery()->getPageSet();
	}

	/**
	 * Convert a title to a DB key
	 * @param $title string Page title with spaces
	 * @return string Page title with underscores
	 */
	public function titleToKey( $title ) {
		# Don't throw an error if we got an empty string
		if ( trim( $title ) == '' )
			return '';
		$t = Title::newFromText( $title );
		if ( !$t )
			$this->dieUsageMsg( array( 'invalidtitle', $title ) );
		return $t->getPrefixedDbKey();
	}

	/**
	 * The inverse of titleToKey()
	 * @param $key string Page title with underscores
	 * @return string Page title with spaces
	 */
	public function keyToTitle( $key ) {
		# Don't throw an error if we got an empty string
		if ( trim( $key ) == '' )
			return '';
		$t = Title::newFromDbKey( $key );
		# This really shouldn't happen but we gotta check anyway
		if ( !$t )
			$this->dieUsageMsg( array( 'invalidtitle', $key ) );
		return $t->getPrefixedText();
	}
	
	/**
	 * An alternative to titleToKey() that doesn't trim trailing spaces
	 * @param $titlePart string Title part with spaces
	 * @return string Title part with underscores
	 */
	public function titlePartToKey( $titlePart ) {
		return substr( $this->titleToKey( $titlePart . 'x' ), 0, - 1 );
	}
	
	/**
	 * An alternative to keyToTitle() that doesn't trim trailing spaces
	 * @param $keyPart string Key part with spaces
	 * @return string Key part with underscores
	 */
	public function keyPartToTitle( $keyPart ) {
		return substr( $this->keyToTitle( $keyPart . 'x' ), 0, - 1 );
	}

	/**
	 * Get version string for use in the API help output
	 * @return string
	 */
	public static function getBaseVersion() {
		return __CLASS__ . ': $Id$';
	}
}

/**
 * @ingroup API
 */
abstract class ApiQueryGeneratorBase extends ApiQueryBase {

	private $mIsGenerator;

	public function __construct( $query, $moduleName, $paramPrefix = '' ) {
		parent :: __construct( $query, $moduleName, $paramPrefix );
		$this->mIsGenerator = false;
	}

	/**
	 * Switch this module to generator mode. By default, generator mode is
	 * switched off and the module acts like a normal query module.
	 */
	public function setGeneratorMode() {
		$this->mIsGenerator = true;
	}

	/**
	 * Overrides base class to prepend 'g' to every generator parameter
	 * @param $paramNames string Parameter name
	 * @return string Prefixed parameter name
	 */
	public function encodeParamName( $paramName ) {
		if ( $this->mIsGenerator )
			return 'g' . parent :: encodeParamName( $paramName );
		else
			return parent :: encodeParamName( $paramName );
	}

	/**
	 * Execute this module as a generator
	 * @param $resultPageSet ApiPageSet: All output should be appended to
	 *  this object
	 */
	public abstract function executeGenerator( $resultPageSet );
}
