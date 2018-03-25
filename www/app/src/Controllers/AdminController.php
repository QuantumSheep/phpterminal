<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Models\AdminModels\AdminTerminalListModel;
use Alph\Services\Database;
use Alph\Managers\AccountManager;

class AdminController
{
    public static function index(array $params)
    {
        return (new View("admin/index"))->render();
    }

    public static function terminal(array $params)
    {
        $db = Database::connect();
        $model = new AdminTerminalListModel();
        $model->terminals = [];
        $model->users = [];

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $model->terminals[] = TerminalManager::getTerminal($db, $params["mac"]);

            if(empty($model->terminals[0])) {
                return header("Location: /admin/terminal");
            }

            $model->users[] = AccountManager::getAccount($db, $model->terminals[0]->account);

            \setcookie("terminal", $params["mac"], 0, "/");
            return (new View("admin/terminal_edit", $model))->render();
        } else {
            $model->terminals = TerminalManager::getTerminals($db) ?? [];

            $accountids = [];

            foreach($model->terminals as &$terminal) {
                $accountids[] = $terminal->account;
            }

            $model->users = AccountManager::getAccounts($db, $accountids);

            return (new View("admin/terminal_list", $model))->render();
        }
    }
}
