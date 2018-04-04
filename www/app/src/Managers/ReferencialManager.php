<?php
namespace Alph\Managers;

use Alph\Models\ReferentialModel;

class ReferencialManager
{
    /**
     * Get the account ID of an account activation code
     */
    public static function getReferencialCategories(\PDO $db)
    {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT idreferencial, type, category, code, value FROM REFERENCIAL;");

        $result = [];
        // Execute the query and check if successful
        if ($stmp->execute()) {
            // Check if there is one row selected
            while($row = $stmp->fetch()) {
                // Fetch and map the row
                $result[] = ReferentialModel::map($row);
            }
        }

        return $result;
    }
}
