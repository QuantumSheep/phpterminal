<?php
namespace Alph\Managers;

use Alph\Models\NetworkModel;

class NetworkManager
{
    /**
     * Create a new network
     */
    public static function createNetwork(\PDO $db)
    {
        // Prepare the SQL row insert
        $stmp = $db->prepare("CALL NewNetwork();");

        // Execute the query
        if ($stmp->execute()) {
            if ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                // Get the network's MAC from the stored procedure
                return $row["@network_mac"];
            }
        }

        return false;
    }

    public static function getNetwork(\PDO $db, string $mac)
    {
        $stmp = $db->prepare("SELECT mac, ipv4, ipv6 FROM NETWORK WHERE mac = :mac;");

        $stmp->bindParam(":mac", $mac);

        $stmp->execute();

        if ($stmp->rowCount() > 0) {
            if ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $network = NetworkModel::map($row);

                return $network;
            }
        }

        return new NetworkModel();
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     */
    public static function getNetworks(\PDO $db, $limit = 10, $offset = 0)
    {
        $sql = "SELECT mac, ipv4, ipv6 FROM NETWORK";

        $isOffset = $offset != null;
        $isLimited = $limit != null;

        if ($isOffset && $isLimited) {
            $sql .= " LIMIT :offset, :limit";
        } else if ($isLimited) {
            $sql .= " LIMIT :limit";
        } else if ($isOffset) {
            $sql .= " OFFSET :offset";
        }

        $stmp = $db->prepare($sql);

        if ($isOffset) {
            $stmp->bindParam(":offset", $offset);
        }

        if ($isLimited) {
            $stmp->bindParam(":limit", $limit, \PDO::PARAM_INT);
        }

        $stmp->execute();

        $networks = [];

        if ($stmp->rowCount() > 0) {
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $networks[$row["mac"]] = NetworkModel::map($row);
            }
        }

        return $networks;
    }

    /**
     * Assign a new private IP to a terminal in a specific network
     *
     * @param string $network Network's mac address
     * @param string $terminal Terminal's mac address
     */
    public static function assignPrivateIP(\PDO $db, string $network, string $terminal)
    {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT ip FROM PRIVATEIP WHERE network = :network ORDER BY ip DESC LIMIT 1;");

        // Bind the query parameters
        $stmp->bindParam(":network", $network);
        $stmp->bindParam(":terminal", $terminal);

        // Execute the SQL command
        $stmp->execute();

        // Pre-define ip
        $ip;

        // Check if there's one IP in the SQL query (limited at one row)
        if ($stmp->rowCount() == 1) {
            // Get the ip address from query's selected row
            $ip = $stmp->fetch()["ip"];

            // Check if the maximum IP has been reached (192.168.255.254)
            if ($ip == "192.168.255.254") {
                return false;
            }

            // Split the ip into 4 logical parts
            $iparr = explode('.', $ip);

            // Check if IP's last part is 255 (the limit)
            if ($iparr[3] == 255) {
                // Increment IP's third part
                $iparr[2]++;

                // Define IP's last part to 1
                $iparr[3] = 1;
            } else {
                // Increment IP's last part
                $iparr[3]++;
            }

            $ip = "192.168." . $iparr[2] . "." . $iparr[3];
        } else {
            // Define the IP to the minimum address assignable
            $ip = "192.168.0.2";
        }

        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO PRIVATEIP (network, terminal, ip) VALUES (:network, :terminal, :ip) ON DUPLICATE KEY UPDATE ip = :ip;");

        // Bind the query parameters
        $stmp->bindParam(":network", $network);
        $stmp->bindParam(":terminal", $terminal);
        $stmp->bindParam(":ip", $ip);

        // Execute the SQL command and return the boolean result
        return $stmp->execute();
    }

    /**
     * Generate a new MAC address
     */
    public static function generateMac(): string
    {
        // Pre-define mac address
        $mac = "";

        // Loop 5 times
        for ($i = 0; $i < 5; $i++, $mac .= "-") {
            // Add a MAC address part
            $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);
        }

        // Add the MAC address last part (to ignore the ':')
        $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);

        // Return the new generated MAC address
        return $mac;
    }

    /**
     * Generate a new public IPv4
     */
    public static function generatePublicIPv4(): string
    {
        // Pre-define ip
        $ip = "";

        // Define a random part (to escape private IP)
        $part = rand(0, 2);

        // Select the IP's first numbers
        switch ($part) {
            case 0:
                $ip .= rand(1, 9);
                break;
            case 1:
                $ip .= rand(11, 126);
                break;
            case 2:
                $ip .= rand(129, 191);
                break;
        }

        // Add random parts to the IP
        $ip .= "." . rand(0, 254) . "." . rand(0, 254) . "." . rand(2, 254);

        // Return the new generated IP address
        return $ip;
    }

    /**
     * Generate a new public IPv6
     */
    public static function generatePublicIPv6(): string
    {

    }

    public static function isMAC(string $str)
    {
        return \preg_match("/^([0-9A-F]{2}[.:-]){5}[0-9A-F]{2}$/i", $str) === 1 ? true : false;
    }

    public static function formatMAC(string $mac)
    {
        return str_replace(['.', ':'], '-', strtoupper($mac));
    }

    public static function formatMACForDatabase(string $mac)
    {
        return str_replace(['.', ':'], '-', strtoupper($mac));
    }
}
