<?php
namespace Alph\Managers;

use Alph\Models\AccountModel;

class AccountManager
{
    /**
     * Check logon content
     */
    public static function checkAccountRegister(\PDO $db, string $username, string $email, string $password, string $password2)
    {
        // Pre-define error list
        $errors = [];

        // Check if the form is completed
        if (empty($username) || empty($email) || empty($password) || empty($password2)) {
            $errors[] = "Please complete the form.";
            return $errors;
        }

        // Check if the username is more than 3 characters
        if (strlen($username) < 3) {
            $errors[] = "The username must contains 3 characters minimum.";
        }

        // Check if the email is valid
        if (!\filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please provide a valid email adress.";
        }

        // Check if the password is more than 8 characters
        if (strlen($password) < 8) {
            $errors[] = "The password must contains 8 characters minimum.";
        }

        // Check if the password is more than 8 characters
        if ($password !== $password2) {
            $errors[] = "The passwords must match.";
        }

        // Check if there are no errors
        if (empty($errors)) {
            // Prepare the SQL row selection
            $stmp = $db->prepare("SELECT email, username FROM ACCOUNT WHERE username = :username OR email = :email");

            // Bind the query parameters
            $stmp->bindParam(':username', $username);
            $stmp->bindParam(':email', $email);

            // Execute the SQL command
            $stmp->execute();

            // Check if there's a select row
            if ($stmp->rowCount() > 0) {
                // Loop over all the rows
                while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                    // If there's already an email or an username matching the user input, declare an error
                    if ($row["email"] == $email) {
                        $errors[] = "This email adress is already used.";
                    } else if ($row["username"] == $username) {
                        $errors[] = "This username is already used.";
                    }
                }
            }
        }

        // Return the errors
        return $errors;
    }

    /**
     * Check if a username already exist.
     */
    public static function usernameExist(\PDO $db, string $username)
    {
        $stmp = $db->prepare("SELECT username FROM ACCOUNT WHERE username = :username");

        // Bind the query parameters
        $stmp->bindParam(':username', $username);

        // Execute the SQL command
        $stmp->execute();

        // Check if there's a select row
        if ($stmp->rowCount() > 0) {
            // Loop over all the rows
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                // If there's already an email or an username matching the user input, declare an error
                if ($row["username"] == $username) {
                    $errors[] = "This username is already used.";
                }
            }
        }

        // Return the errors
        return $errors;
    }

    /**
     * Create a new user
     */
    public static function createAccount(\PDO $db, string $username, string $email, string $password)
    {
        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO ACCOUNT (status, hyperpower, email, username, password, code, createddate, editeddate) VALUES(0, 0, :email, :username, :password, :code, NOW(),  NOW())");

        // Get a new alphanumeric code
        $code = randomAlphanumeric(100);

        // Bind the query parameters
        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":username", $username);
        $stmp->bindParam(":code", $code);

        // Crypt the password
        $password = \password_hash($password, PASSWORD_BCRYPT);
        $stmp->bindParam(":password", $password);

        // Execute the query and verify if is right done
        if ($stmp->execute()) {
            return $code;
        }

        return false;
    }

    public static function countAccounts(\PDO $db, string $search = null)
    {
        $sql = "SELECT COUNT(idaccount) as c FROM ACCOUNT";

        if ($search !== null) {
            $sql .= " WHERE username LIKE CONCAT('%', :search ,'%') OR email LIKE CONCAT('%', :search ,'%')";
        }

        $stmp = $db->prepare($sql);

        if ($search !== null) {
            $stmp->bindParam(":search", $search);
        }

        $stmp->execute();

        if ($stmp->rowCount() > 0) {
            if ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                return $row["c"];
            }
        }

        return 0;
    }

    /**
     * @return AccountModel[]
     */
    public static function getAccounts(\PDO $db, $limit = 10, $offset = 0, string $search = null)
    {
        $sql = "SELECT idaccount, status, email, username FROM ACCOUNT";

        $isOffset = $offset != null && $offset > 0;
        $isLimited = $limit != null;

        if ($search !== null) {
            $search = \str_replace('%', "\\%", $search);
            $sql .= " WHERE username LIKE CONCAT('%', :search ,'%') OR email LIKE CONCAT('%', :search ,'%')";
        }

        if ($isOffset && $isLimited) {
            $sql .= " LIMIT :offset, :limit";
        } else if ($isLimited) {
            $sql .= " LIMIT :limit";
        } else if ($isOffset) {
            $sql .= " OFFSET :offset";
        }

        $stmp = $db->prepare($sql);

        if ($search !== null) {
            $stmp->bindParam(":search", $search);
        }

        if ($isOffset) {
            $stmp->bindParam(":offset", $offset, \PDO::PARAM_INT);
        }

        if ($isLimited) {
            $stmp->bindParam(":limit", $limit, \PDO::PARAM_INT);
        }

        $stmp->execute();

        $accounts = [];

        if ($stmp->rowCount() > 0) {
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $accounts[$row["idaccount"]] = AccountModel::map($row);
            }

            return $accounts;
        }

        return $accounts;
    }

    public static function getAccountById(\PDO $db, int $idaccount)
    {
        $account = self::getAccountsById($db, [$idaccount]);

        if (!empty($account)) {
            return reset($account);
        }

        return [];
    }

    /**
     * Get values of multiple accounts by their ID
     */
    public static function getAccountsById(\PDO $db, array $idaccounts)
    {
        // Prepare SQL row selection
        $stmp = $db->prepare("SELECT idaccount, status, hyperpower, email, username, createddate, editeddate FROM ACCOUNT WHERE idaccount = :idaccount;");

        $accounts = [];

        foreach ($idaccounts as &$idaccount) {
            // Bind email parameter
            $stmp->bindParam(":idaccount", $idaccount);

            if ($stmp->execute()) {
                if ($stmp->rowCount() == 1) {
                    $row = $stmp->fetch();

                    $accounts[$idaccount] = AccountModel::map($row);
                }
            }
        }

        return $accounts;
    }

    /**
     * Validate an account
     */
    public static function validateAccount(\PDO $db, int $idaccount)
    {
        // Preapre the SQL row update
        $stmp = $db->prepare("UPDATE ACCOUNT SET status=1 WHERE idaccount = :idaccount");

        // Bind the idaccount parameter
        $stmp->bindParam(":idaccount", $idaccount);

        // Execute the query and return it (boolean)
        return $stmp->execute();
    }

    /**
     * Get the account ID of an account activation code
     */
    public static function getAccountIdFromCode(\PDO $db, string $code)
    {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT idaccount FROM ACCOUNT WHERE code = :code;");

        // Bind the code parameter
        $stmp->bindParam(":code", $code);

        // Execute the query and check if successful
        if ($stmp->execute()) {
            // Check if there is one row selected
            if ($stmp->rowCount() == 1) {
                // Return the account ID
                return $stmp->fetch()["idaccount"];
            }
        }

        return false;
    }

    /**
     * Delete an account validation code from the database
     */
    public static function removeValidationCode(\PDO $db, string $code)
    {
        // Prepare the SQL row deletion
        $stmp = $db->prepare("UPDATE ACCOUNT SET code = NULL WHERE code = :code;");

        // Bind the code parameter
        $stmp->bindParam(":code", $code);

        // Execute the query and return it (boolean)
        return $stmp->execute();
    }

    /**
     * Verify the account informations
     */
    public static function checkAccountLogin(string $email, string $password)
    {
        // Pre-define the errors array
        $errors = [];

        // Check if the password is more than 8 characters
        if (strlen($password) < 8) {
            $errors[] = "You have entered an invalid username or password.";
        }

        // Check if the email is valid
        if (!\filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please provide a valid email adress.";
        }

        // Returns the errors
        return $errors;
    }

    /**
     * Connect an account and register it in a session
     */
    public static function identificateAccount(\PDO $db, string $email, string $password)
    {
        // Prepare SQL row selection
        $stmp = $db->prepare("SELECT hyperpower, idaccount, email, username, password, createddate, editeddate FROM ACCOUNT WHERE email = :email AND status=1;");

        // Bind email parameter
        $stmp->bindParam(":email", $email);

        if ($stmp->execute()) {
            if ($stmp->rowCount() == 1) {
                $row = $stmp->fetch();

                // Check if the passwords match
                if (!\password_verify($password, $row["password"])) {
                    return false;
                }

                $row["password"] = null;

                // Store the account properties in the session (casted to an array)
                $_SESSION["account"] = AccountModel::map($row);

                return true;
            }
        }

        return false;
    }

    public static function editAccount(\PDO $db, int $idaccount, AccountModel $account)
    {
        if ($account->email != null || !empty($account->email)) {
            $stmp = $db->prepare("UPDATE account SET email = :email WHERE idaccount= :idaccount;");
            $stmp->bindParam(":idaccount", $idaccount);
            $stmp->bindParam(":email", $account->email);
        } else if ($account->username != null || !empty($account->username)) {
            $stmp = $db->prepare("UPDATE account SET username = :username WHERE idaccount= :idaccount;");
            $stmp->bindParam(":idaccount", $idaccount);
            $stmp->bindParam(":username", $account->username);
        } else if ($account->password != null || !empty($account->password)) {
            $stmp = $db->prepare("UPDATE account SET password = :password WHERE idaccount= :idaccount;");
            $stmp->bindParam(":idaccount", $idaccount);
            $stmp->bindParam(":password", $account->password);
        }
        $stmp->execute();
    }

    public static function isConnected()
    {
        return isset($_SESSION["account"]) && !empty($_SESSION["account"]->idaccount);
    }

    public static function isAdmin()
    {
        return isset($_SESSION["account"]) && $_SESSION["account"]->hyperpower;
    }

    /**
     * Logout an account
     */
    public static function logout()
    {
        unset($_SESSION["account"]);
    }
}
