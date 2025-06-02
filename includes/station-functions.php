<?php
/**
 * Station related functions
 */

/**
 * Get all stations
 *
 * @param bool $activeOnly If true, return only active stations (currently unused)
 * @return array Array of stations
 */
function getAllStations($activeOnly = true) {
    $sql = "SELECT * FROM Stations";
    return fetchAll($sql, []);
}

/**
 * Get station details
 *
 * @param int $stationId Station ID
 * @return array|null Station details or null if not found
 */
function getStationDetails($stationId) {
    return fetchOne("SELECT * FROM Stations WHERE station_id = ?", [$stationId]);
}

/**
 * Get all ports for a station
 *
 * @param int $stationId Station ID
 * @return array Array of ports with their status
 */
function getStationPorts($stationId) {
    $sql = "SELECT p.port_id, p.status,
                   cp.charging_point_id,
                   COUNT(*) OVER () as total_ports,
                   SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) OVER () as available_ports
            FROM Ports p
            JOIN Charging_Points cp ON p.charging_point_id = cp.charging_point_id
            WHERE cp.station_id = ?
            ORDER BY cp.charging_point_id, p.port_id";
            
    return fetchAll($sql, [$stationId]);
}

/**
 * Get charging point details
 *
 * @param int $chargingPointId Charging point ID
 * @return array|null Charging point details with station info or null if not found
 */
function getChargingPointDetails($chargingPointId) {
    $sql = "SELECT cp.*, 
                   s.station_id, 
                   s.address_street, 
                   s.address_city,
                   s.address_municipality, 
                   s.address_civic_num, 
                   s.address_zipcode,
                   GROUP_CONCAT(p.status) as port_states
            FROM Charging_Points cp
            JOIN Stations s ON cp.station_id = s.station_id
            LEFT JOIN Ports p ON cp.charging_point_id = p.charging_point_id
            WHERE cp.charging_point_id = ?
            GROUP BY cp.charging_point_id";

    return fetchOne($sql, [$chargingPointId]);
}

/**
 * Get available charging points at a station
 *
 * @param int $stationId Station ID
 * @return array Array of available charging points
 */
function getAvailableChargingPoints($stationId) {
    $sql = "SELECT cp.*, 
                   COUNT(p.port_id) as total_ports,
                   SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available_ports
            FROM Charging_Points cp
            LEFT JOIN Ports p ON cp.charging_point_id = p.charging_point_id
            WHERE cp.station_id = ? AND p.status = 'available'
            GROUP BY cp.charging_point_id
            HAVING available_ports > 0";
            
    return fetchAll($sql, [$stationId]);
}