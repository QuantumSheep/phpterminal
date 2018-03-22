<?php
namespace Alph\Managers;

class AccountManager
{
    /**
     * Check logon content
     */
    public static function checkAccountRegister(\PDO $db, string $username, string $email, string $password)
    {
        // Pre-define error list
        $errors = [];

        // Check if the form is completed
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "Please complete the form.";
            return $errors;
        }

        // Check if the password is more than 8 characters
        if (strlen($password) < 8) {
            $errors[] = "The password must contains 8 characters minimum.";
        }

        // Check if the username is more than 3 characters
        if (strlen($username) < 3) {
            $errors[] = "The username must contains 3 characters minimum.";
        }

        // Check if the email is valid
        if (!preg_match("/[a-zA-Z0-9.!#$%&'*+\/=?^_``{|}~-]+@[a-zA-Z0-9^_\-\.%+]+\.[a-zA-Z0-9]{2,8}$/", $email)) {
            $errors[] = "Please provide a valid email adress.";
        }

        // Check if there are no errors
        if (empty($errors)) {
            // Prepare the SQL row selection
            $stmp = $db->prepare("SELECT idaccount FROM ACCOUNT WHERE username = :username OR email = :email");

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
     * Create a new user
     */
    public static function createAccount(\PDO $db, string $username, string $email, string $password)
    {
        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO ACCOUNT (status, email, username, password, createddate, editeddate) VALUES(0, :email, :username, :password, NOW(),  NOW())");

        // Bind the query parameters
        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":username", $username);

        // Crypt the password
        $password = \password_hash($password, PASSWORD_BCRYPT);
        $stmp->bindParam(":password", $password);

        // Execute the query and return it (boolean)
        return $stmp->execute();
    }

    /**
     * Create an account activation code
     */
    public static function createActivationCode(\PDO $db, string $email)
    {
        // Define the allowed characters
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

        // Get the characters string length
        $characters_length = strlen($characters);

        // Pre-define rand_str to an empty string
        $rand_str = "";

        // Loop 100 times
        for ($i = 0; $i < 100; $i++) {
            // Add a random character to the random string
            $rand_str .= $characters[rand(0, $characters_length - 1)];
        }

        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO ACCOUNT_VALIDATION (idaccount, code) VALUES((SELECT idaccount FROM ACCOUNT WHERE email = :email), :code);");

        // Bind the query parameters
        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":code", $rand_str);

        // Execute the query and return the random string if successful
        if ($stmp->execute()) {
            return $rand_str;
        }

        return false;
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
        $stmp = $db->prepare("SELECT idaccount FROM ACCOUNT_VALIDATION WHERE code = :code;");

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
        $stmp = $db->prepare("DELETE FROM ACCOUNT_VALIDATION WHERE code = :code;");

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
            $errors[] = "Incorrect passord.";
        }

        // Check if the email is valid
        if (!preg_match("/[a-zA-Z0-9.!#$%&'*+\/=?^_``{|}~-]+@[a-zA-Z0-9^_\-\.%+]+\.[a-zA-Z0-9]{2,8}$/", $email)) {
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
        $stmp = $db->prepare("SELECT idaccount, email, username, password FROM account WHERE email = :email AND status=1;");

        // Bind email parameter
        $stmp->bindParam(":email", $email);

        if ($stmp->execute()) {
            if ($stmp->rowCount() == 1) {
                $user = $stmp->fetch();

                // Check if the passwords match
                if (!\password_verify($password, $user["password"])) {
                    return false;
                }

                // Store the account properties in the session
                $_SESSION["account"]["idaccount"] = $user["idaccount"];
                $_SESSION["account"]["email"] = $user["email"];
                $_SESSION["account"]["username"] = $user["username"];

                return true;
            }
        }

        return false;
    }

    /**
     * Logout an account
     */
    public static function logout()
    {
        unset($_SESSION["account"]);
    }
}
