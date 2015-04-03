<?php
/**
 * Created by PhpStorm.
 * User: Jari
 * Date: 2.4.2015
 * Time: 23:07
 */

/**
 * Connect to database
 *
 * @param $dbHost
 * @param $dbUser
 * @param $dbPass
 * @return mysqli
 */
function db_connect($dbHost, $dbUser, $dbPass)
{
    return mysqli_connect($dbHost, $dbUser, $dbPass);
}

/**
 * Set DB for the connection
 *
 * @param $dbDatabase   string  Database name
 * @param $db           object  Database handle
 * @return bool
 */
function db_select_db($dbDatabase, $db)
{
    return mysqli_select_db($db, $dbDatabase);
}

/**
 * DB query for data
 *
 * @param $db          object  Database handle
 * @param $query       string  SQL query
 * @param $param_types string
 *                     Character    Description
 *                     i    corresponding variable has type integer
 *                     d    corresponding variable has type double
 *                     s    corresponding variable has type string
 *                     b    corresponding variable is a blob and will be sent in packets
 * @return array|null
 */
function db_query($db, $query, $param_types = null)
{
    $params = array_slice(func_get_args(), 3);
    if ($param_types && $params) {
        // There are parameters, bind them.
        $stmt = mysqli_stmt_init($db);
        #mysqli_stmt_bind_param($stmt, $param_types, $params);
        mysqli_stmt_prepare($stmt, $query) or
        die("Bad statement!");

        $bind_params = array();
        $bind_params[] = $stmt;
        $bind_params[] = $param_types;
        foreach ($params as $idx => $param) {
            $bind_params[] = &$params[$idx];
        }
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
        mysqli_stmt_execute($stmt) or
        die("Failed to execute query!");
        $result = mysqli_stmt_get_result($stmt);
    } else {
        // No parameters-style query.
        $result = mysqli_query($db, $query);
    }

    return $result;
}

/**
 * Return DB result as mixed array.
 * There are numeric and associative indexes in the returned array.
 *
 * @param $result
 * @return array|null
 */
function db_fetch_array($result)
{
    return mysqli_fetch_array($result);
}

/**
 * Return DB result as an associative array
 * @param $result
 * @return array|null
 */
function db_fetch_array_assoc($result)
{
    return mysqli_fetch_assoc($result);
}

/**
 * DB modify data
 *
 * @param       $db
 * @param       $query
 * @param       $param_types
 * @param array $params
 * @return int number of rows affected
 */
function db_modify($db, $query, $param_types = null)
{
    $params = array_slice(func_get_args(), 3);
    if ($param_types && $params) {
        // There are parameters, bind them.
        $stmt = mysqli_stmt_init($db);
        #mysqli_stmt_bind_param($stmt, $param_types, $params);
        mysqli_stmt_prepare($stmt, $query) or
        die("Bad statement!");

        $bind_params = array();
        $bind_params[] = $stmt;
        $bind_params[] = $param_types;
        foreach ($params as $idx => $param) {
            $bind_params[] = &$params[$idx];
        }
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
        mysqli_stmt_execute($stmt) or
        die("Failed to execute query!");
        $rows_affected = mysqli_stmt_affected_rows($stmt);
    } else {
        // No parameters-style query.
        mysqli_query($db, $query);
        $rows_affected = mysqli_affected_rows($db);
    }

    return $rows_affected;
}

/**
 * @param $result
 * @return int
 */
function db_num_rows($result)
{
    return mysqli_num_rows($result);
}