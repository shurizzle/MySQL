<?php
/**
 * MySQL
 *
 * Class to simplify your life managing MySQL.
 *
 * @author shura <shura1991@gmail.com>
 * @version 0.1
 * @package MySQL
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL
 */

/**
 * Managing MySQL results class.
 */
class MySQLResult { /* {{{ */
    /* variables {{{ */
    /**#@+
     * @access private
     */
    /**
     * Variable that contains the result of query
     * @var resource|NULL
     */
    protected $resource = NULL;
    /**#@+
     * @var integer
     */
    /**
     * Number of rows
     */
    protected $rowsNo = 0;
    /**
     * Number of fields
     */
    protected $fieldsNo = 0;
    /**
     * Current position
     */
    protected $index = -1;
    /**#@-*/
    /**#@+
     * @var array
     */
    /**
     * Field arranged by position (byPos) and by name (byName)
     */
    protected $fields = array();
    /**
     * Associative Array, keys are the field name and values are the values
     */
    protected $assoc = array();
    /**
     * Numeric Array, values are arranged like in the query
     */
    protected $num = array();
    /**#@-*/
    /**#@-*/
    /* }}} */

    /* __construct {{{ */
    /**
     * Constructor of MySQLResult
     *
     * Sets {@link $resource} with given parameter, {@link $rowsNo} with number
     * of rows, {@link $fieldsNo} with the number of field and {@link $fields}
     * with name of fields
     * @param resource $resource the result of the query
     */
    public function
    __construct($resource) {
        if (!is_resource($resource))
            throw new Exception("Given parameter is NOT a resource.");
        $this->resource = $resource;
        $this->rowsNo = mysql_num_rows($resource);
        $this->fieldsNo = mysql_num_fields($resource);
        for ($i = 0; $i < $this->fieldsNo; $i++) {
            $name = mysql_field_name($resource, $i);
            $this->fields['byPos'][$i] = $name;
            $this->fields['byName'][$name] = $i;
        }
    }
    /* }}} */

    /* parse {{{ */
    /**
     * Parse a line
     * This method set {@link $assoc}, {@link $num} and other attributes called
     * like fields name. Es:
     * <code>
     * $db = new MySQL('host', 'user', 'pass', 'database');
     * $res = $db->query('SELECT id, name FROM users;');
     * while ($res->next()) {
     *     echo $res->id . " => " . $res->name . "\n";
     * }
     * </code>
     */
    protected function
    parse() {
        for ($i = 0; $i < $this->fieldsNo; $i++) {
            $value = mysql_result($this->resource, $this->index, $i);
            $name = $this->fields['byPos'][$i];
            $this->assoc[$name] = $value;
            $this->num[$i] = $value;
            $this->$name = $value;
        }
    }
    /* }}} */

    /* reset {{{ */
    /**
     * Unset all elements
     * This method unset {@link $assoc}, {@link $num} and the other attributes.
     */
    protected function
    reset() {
        for ($i = 0; $i < $this->fieldsNo; $i++) {
            $name = $this->fields['byPos'][$i];
            unset($this->assoc[$name]);
            unset($this->num[$i]);
            unset($this->$name);
        }
    }
    /* }}} */

    /* seek {{{ */
    /**
     * Seek at the given position
     * If given position is in the range of positions (0..{@link $rowsNo}) set
     * {@link $index} to it and use {@link parse()}.
     */
    public function
    seek($pos) {
        if ($pos > -1 && $pos < $this->rowsNo) {
            $this->index = $pos;
            $this->parse();
            return true;
        }
        return false;
    }
    /* }}} */

    /* next {{{ */
    /**
     * Seek at the following row.
     * If exists a following row it increment {@link $index}, use {@link
     * parse()} and return true, else use {@link reset()} and return false.
     * @return boolean
     */
    public function
    next() {
        if (!($this->index < $this->rowsNo)) {
            $this->reset();
            return false;
        }
        $this->index++;
        if (!($this->index < $this->rowsNo)) {
            $this->reset();
            return false;
        }
        $this->parse();
        return true;
    }
    /* }}} */

    /* prev {{{ */
    /**
     * Seek at the previous row.
     * If exists a previous row it decrement {@link $index}, use {@link parse()}
     * and return true, else use {@link reset()} and return false.
     * @return boolean
     */
    public function
    prev() {
        if (!($this->index > -1)) {
            $this->reset();
            return false;
        }
        $this->index--;
        if (!($this->index > -1)) {
            $this->reset();
            return false;
        }
        $this->parse();
        return true;
    }
    /* }}} */

    /* getAssoc {{{ */
    /**#@+
     * @return array
     */
    /**
     * Return Associative Array.
     */
    public function
    getAssoc() {
        return $this->assoc;
    }
    /* }}} */

    /* getNum {{{ */
    /**
     * Return Numeric Array.
     */
    public function
    getNum() {
        return $this->num;
    }
    /* }}} */

    /* getBoth {{{ */
    /**
     * Return both Numeric and Associative Arrays merged.
     */
    public function
    getBoth() {
        return $this->assoc + $this->num;
    }
    /**#@-*/
    /* }}} */

    /* rowsNo {{{ */
    /**
     * Get the number of present rows.
     * @return integer
     */
    public function
    rowsNo() {
        return $this->rowsNo;
    }
    /* }}} */

    /* fieldsNo {{{ */
    /**
     * Get the number of present fields.
     * @return integer
     */
    public function
    fieldsNo() {
        return $this->fieldsNo;
    }
    /* }}} */

    /* fieldsNames {{{ */
    /**
     * Get all fields' names in an array.
     * @return array
     */
    public function
    fieldsNames() {
        return array_keys($this->fields['byName']);
    }
    /* }}} */

    /* fieldInfo {{{ */
    /**#@+
     * @param string|integer $field Is the field's name or number (in order).
     */
    /**
     * Returns an stdClass object containing field information.
     * The properties of the object are:
     * - name - column name
     * - table - name of the table the column belongs to
     * - max_length - maximum length of the column
     * - not_null - 1 if the column cannot be NULL
     * - primary_key - 1 if the column is a primary key
     * - unique_key - 1 if the column is a unique key
     * - multiple_key - 1 if the column is a non-unique
     * - key
     * - numeric - 1 if the column is numeric
     * - blob - 1 if the column is a BLOB
     * - type - the type of the column
     * - unsigned - 1 if the column is
     * - unsigned
     * - zerofill - 1 if the column is
     * - zero-filled
     * @return stdClass
     */
    public function
    fieldInfo($field) {
        $field = $this->fields['byName'][$field] || $field;
        if (is_null($this->fields['byPos'][$field]))
            return NULL;
        return mysql_fetch_field($this->resource, $field);
    }
    /* }}} */

    /* fieldName {{{ */
    /**
     * Get the name of a given field's position.
     * @param integer $field Is the field's number (in order).
     * @return string Name of the field position.
     */
    public function
    fieldName($field) {
        return $this->fields['byPos'][$field];
    }
    /* }}} */

    /* fieldType {{{ */
    /**
     * Get the type of field (one of: "int", "real", "string", "blob").
     * @return string The type of the field.
     */
    public function
    fieldType($field) {
        $field = $this->fields['byName'][$field] || $field;
        if (is_null($this->fields['byPos'][$field]))
            return NULL;
        return mysql_field_type($this->resource, $field);
    }
    /* }}} */

    /* fieldLen {{{ */
    /**
     * Get the length of given field.
     * @return integer Return the length of given field.
     */
    public function
    fieldLen($field) {
        $field = $this->fields['byName'][$field] || $field;
        if (is_null($this->fields['byPos'][$field]))
            return NULL;
        return mysql_field_len($this->resource, $field);
    }
    /* }}} */

    /* fieldFlags {{{ */
    /**
     * Get the MySQL flags of given field.
     * @return string The field flags of the given field separated by a space.
     */
    public function
    fieldFlags($field) {
        $field = $this->fields['byName'][$field] || $field;
        if (is_null($this->fields['byPos'][$field]))
            return NULL;
        return mysql_field_flags($this->resource, $field);
    }
    /* }}} */

    /* fieldTable {{{ */
    /**
     * Get the name of the table that the given field is in.
     * @return string Return the table of given field is in.
     */
    public function
    fieldTable($field) {
        $field = $this->fields['byName'][$field] || $field;
        if (is_null($this->fields['byPos'][$field]))
            return NULL;
        return mysql_field_table($this->resource, $field);
    }
    /**#@-*/
    /* }}} */

    /* close {{{ */
    /**
     * Free space allocated for {@link $resource}.
     */
    public function
    close() {
        mysql_free_result($this->resource);
    }
    /* }}} */

    /* __destruct {{{ */
    /**
     * Destructor of class.
     * Call {@link close()}.
     */
    public function
    __destruct() {
        $this->close();
    }
    /* }}} */
}
/* }}} */

/**
 * MySQL main class.
 */
class MySQL { /* {{{ */
    /* variables {{{ */
    /**#@+
     * @access protected
     */
    /**
     * Variable that store the MySQL connection.
     * @var resource
     */
    protected $connection = NULL;
    /**
     * Number of queries executed.
     * @var integer
     */
    protected $noQueries = 0;
    /**
     * An array of hashes like:
     * <code>
     * array(
     *     'query'     => 'string',
     *     'time'      => 'integer',
     *     'status'    => 'string (successful|unsuccessful)'
     * );
     * </code>
     * @var array
     */
    protected $queries = array();
    /**#@-*/
    private $lastTime = NULL;
    /* }}} */

    /* microtime {{{ */
    private function
    microtime() {
        return preg_replace ('/^0(\.\d+) (\d+)$/', '\2\1', microtime ());
    }
    /* }}} */

    /* benchmark_start {{{ */
    /**
     * Store current time and push the query in {@link $queries}.
     * @param string $query Query which is executing.
     */
    protected function
    benchmark_start($query) {
        $this->lastTime = $this->microtime();
        $this->queries[] = array(
                'query'     => $query,
                'time'      => NULL,
                'status'    => NULL
                );
    }
    /* }}} */

    /* benchmark_stop {{{ */
    /**
     * Store query's time and status and increment the query count.
     * @param boolean $success If query is succeded true, else false.
     */
    protected function
    benchmark_stop($success = true) {
        $last = count($this->queries) - 1;
        $this->queries[$last]['time'] = $this->microtime() - $this->lastTime;
        $this->lastTime = NULL;
        $this->queries[$last]['status'] = ($success ? '' : 'un') . 'successful';
        $this->noQueries++;
    }
    /* }}} */

    /* getQueries {{{ */
    /**
     * Get {@link $queries}.
     * @return array All executed queries.
     */
    public function
    getQueries() {
        return $this->queries;
    }
    /* }}} */

    /* getQueriesNo {{{ */
    /**
     * Get {@link noQueries}.
     * @return integer Number of executed queries.
     */
    public function
    getQueriesNo() {
        return $this->noQueries;
    }
    /* }}} */

    /* getTotalTime {{{ */
    /**
     * Get the time used to execute all queries.
     * @return integer Time for executing all queries.
     */
    public function
    getTotalTime() {
        $time = 0;
        foreach ($this->queries AS $query) {
            $time += $query['time'];
        }

        return $time;
    }
    /* }}} */

    /* throwError {{{ */
    private function
    throwError() {
        if ($this->lastTime)
            $this->benchmark_stop(false);
        throw new Exception("MySQL ERROR: ".
                mysql_errno($this->connection) . ": " .
                mysql_error($this->connection));
    }
    /* }}} */

    /* isValidString {{{ */
    private function
    isValidString($string) {
        return (is_string($string) && !empty($string));
    }
    /* }}} */

    /* checkConnection {{{ */
    /**
     * Check if {@link $connection} is a valid resource.
     */
    protected function
    checkConnection() {
        if (!is_resource($this->connection))
            throw new Exception("MySQL ERROR: Connection not established");
    }
    /* }}} */

    /* __construct {{{ */
    /**
     * Constructor of the class.
     * Start connection to MySQL server, store in {@link $connection} and select
     * the database if given.
     * @param string $host      Host to connect to.
     * @param string $user      User who log.
     * @param string $pass      Pass of user (obmit or empty if there isn't).
     * @param string $database  Database name (NULL or empty if don't want to select db).
     */
    public function
    __construct($host, $user, $pass = "", $database = NULL) {
        if (!($this->connection = mysql_connect($host, $user, $pass)))
            $this->throwError();
        if ($database)
            $this->selectDB($database);
    }
    /* }}} */

    /* selectDB {{{ */
    public function
    /**
     * Select a database.
     * @param string $database Database to use.
     */
    selectDB($database) {
        $this->checkConnection();
        if ($this->isValidString($database))
            mysql_select_db($database, $this->connection) || $this->throwError();
    }
    /* }}} */

    /* compileStatement {{{ */
    /**
     * Fill statement with given arguments.
     * @param array $args An array of strings, minimum 1 elements, format for first.
     */
    protected function
    compileStatement($args) {
        if (count($args) < 1)
            throw new Exception("Not enough arguments given");

        $statement = array_shift($args);
        
        /* Grep all strings and replace with ?s {{{ */
        preg_match_all('/((["\'])(?:(?!\2)[^\\\\]|\\\\.)*\2)/', $statement, $strings);
        $strings = $strings[1];
        $statement = preg_replace('/(["\'])(?:(?!\1)[^\\\\]|\\\\.)*\1/', '?s', $statement);
        /* }}} */

        $connection = $this->connection;
        return preg_replace_callback('/\?s?/',
                /* replace strings and arguments {{{ */
                function($matches) use (&$strings, &$args, $connection) {
                    if ($matches[0] == '?s') {
                        if (empty($strings))
                            throw new Exception("Your query has one or many errors");
                        return array_shift($strings);
                    } else {
                        if (empty($args))
                            throw new Exception("Not enough arguments given to fill the statement");
                        return "'" . mysql_real_escape_string(array_shift($args), $connection) . "'";
                    }
                },
                /* }}} */
                $statement);
    }
    /* }}} */

    /* query {{{ */
    /**#@+
     * @param string $format,... Insert first the format and after all parameters in order.
     */
    /**
     * Execute a query and give result.
     *
     * Compile the given statement with arguments, execute query and return the
     * result. Example:
     * <code>
     * $db = new MySQL('host', 'user', 'pass', 'database');
     * $res = $db->query('SELECT * FROM users WHERE name = ? AND surname = ?;',
     *     $name, $surname);
     * while ($res->next()) {
     *     print_r($res->getAssoc());
     * }
     * </code>
     *
     * @return MySQLResult Result of the query.
     */
    public function
    query() {
        $this->checkConnection();
        $query = $this->compileStatement(func_get_args());
        $this->benchmark_start($query);
        ($result = mysql_query($query, $this->connection)) || $this->throwError();
        $this->benchmark_stop();
        return new MySQLResult($result);
    }
    /* }}} */

    /* exec {{{ */
    /**
     * Execute a query.
     * The same thing of {@link query()} but don't return any results.
     */
    public function
    exec() {
        $this->checkConnection();
        $query = $this->compileStatement(func_get_args());
        $this->benchmark_start($query);
        ($result = mysql_query($query, $this->connection)) || $this->throwError();
        $this->benchmark_stop();
        mysql_free_result($result);
    }
    /* }}} */

    /* execBatch {{{ */
    /**
     * Execute query without throwing errors.
     * The same thing of {@link exec()} but don't throw exception in case of
     * errors.
     */
    public function
    execBatch() {
        $this->checkConnection();
        $query = $this->compileStatement(func_get_args());
        $this->benchmark_start($query);
        $result = mysql_unbuffered_query($query, $this->connection);
        $this->benchmark_stop();
        mysql_free_result($result);
    }
    /**#@-*/
    /* }}} */

    /* affectedRows {{{ */
    /**
     * Get the number of affected rows by the last query.
     * @return integer Number of affected rows by the last query.
     */
    public function
    affectedRows() {
        $this->checkConnection();
        return mysql_affected_rows($this->connection);
    }
    /* }}} */

    /* encoding {{{ */
    /**
     * Get the character_set variable from MySQL. 
     * @return string Encoding.
     */
    public function
    encoding() {
        $this->checkConnection();
        return mysql_client_encoding($this->connection);
    }
    /* }}} */

    /* setCharset {{{ */
    /**
     * Sets the default character set for the current connection.
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function
    setCharset($charset) {
        $this->checkConnection();
        return mysql_set_charset($charset, $this->connection);
    }
    /* }}} */

    /* ping {{{ */
    /**
     * Ping a server connection or reconnect if there is no connection.
     */
    public function
    ping() {
        mysql_ping($this->connection);
    }
    /* }}} */

    /* stat {{{ */
    /**
     * Get current system status.
     * @return string Returns a string with the status for uptime, threads, queries, open tables, flush tables and queries per second.
     */
    public function
    stat() {
        $this->checkConnection();
        return mysql_stat($this->connection);
    }
    /* }}} */

    /* close {{{ */
    /**
     * Close MySQL connection.
     */
    public function
    close() {
        if (is_resource($this->connection))
            mysql_close($this->connection);
    }
    /* }}} */

    /* getClientInfo {{{ */
    /**
     * Get MySQL client info.
     * @return string MySQL client version.
     */
    public function
    getClientInfo() {
        return mysql_get_client_info();
    }
    /* }}} */

    /* getHostInfo {{{ */
    /**
     * Get MySQL host info.
     * @return string A string describing the type of MySQL connection in use for the connection.
     */
    public function
    getHostInfo() {
        $this->checkConnection();
        return mysql_get_host_info($this->connection) || $this->throwError();
    }
    /* }}} */

    /* getProtoInfo {{{ */
    /**
     * Get MySQL protocol info.
     * @return string MySQL protocol.
     */
    public function
    getProtoInfo() {
        $this->checkConnection();
        return mysql_get_proto_info($this->connection) || $this->throwError();
    }
    /* }}} */

    /* getServerInfo {{{ */
    /**
     * Get MySQL server info.
     * @return string The MySQL server version.
     */
    public function
    getServerInfo() {
        $this->checkConnection();
        return mysql_get_server_info($this->connection) || $this->throwError();
    }
    /* }}} */

    /* lastId {{{ */
    /**
     * Get the ID generated in the last query
     * @return integer The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value.
     */
    public function
    lastId() {
        $this->checkConnection();
        return mysql_insert_id($this->connection) || $this->throwError();
    }
    /* }}} */

    /* __destuct {{{ */
    /**
     * Class destructor.
     * Call {@link close()} method.
     */
    public function
    __destruct() {
        $this->close();
    }
    /* }}} */
}
/* }}} */

// vim:fdm=marker
?>
