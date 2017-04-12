<?php

/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/11/2017
 * Time: 11:44 AM
 */
require_once 'classes/Database.php';
const M_WSS_EVENT_PROCESS = "WSS_EVENT";
const M_API_ROOM_CREATE = "API_ROOM_CREATE";
const M_API_USER_CREATE = "API_USER_CREATE";
const M_API_EVENT = "API_ACCESS";
const M_WSS_EVENT_CHAT = "WSS_EVENT_CHAT";
class PerformanceMetricsLogger
{
    const PM_SQL_INSERT = "INSERT INTO PerformanceMetrics (Event, ProcessTime, Other) VALUES(:type, :time, :other)";
    const PM_SQL_AGGREGATE = "SELECT Event, ROUND(AVG(ProcessTime),2) as AvgTime FROM (SELECT Event, ProcessTime FROM PerformanceMetrics WHERE Time > CURDATE() - INTERVAL 1 HOUR) as RecentMetrics GROUP BY Event";
    private static $insert_statement = NULL;
    public static function Log($metric_type, $process_time, $other){
        if(!PerformanceMetricsLogger::$insert_statement) PerformanceMetricsLogger::$insert_statement = Database::connect()->prepare(PerformanceMetricsLogger::PM_SQL_INSERT);

        PerformanceMetricsLogger::$insert_statement->execute([
            ":type" => $metric_type,
            ":time" => $process_time,
            ":other" => $other,
        ]);
    }

    public static function GetRecentMetrics(){
        $stmt = Database::connect()->prepare(PerformanceMetricsLogger::PM_SQL_AGGREGATE);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}