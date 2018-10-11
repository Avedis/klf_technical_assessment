<?php
class User
{
    private $connection;

    // fields that are to be mass assigned
    private $fields = [
        'dt_modified',
        'first_name', 'last_name', 'job_title', 'email', 'address_1', 'address_2',
        'city', 'postal_code', 'province', 'country', 'phone', 'password', 'salt',
        'date_of_birth', 'disable', 'reset_password', 'role_id'
    ];

    // use dependancy injection to get database connection
    public function __construct(\PDO $pdo)
    {
        $this->connection = $pdo;
    }

    // select a user by id
    function select($user_id)
    {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        var_dump($user);
    }

    // I hardcoded a salt because it has no defaults
    // password_hash() already has a salt built in
    function insert($data)
    {
        // validation fails
        if(!$this->validateUser($data))
            return;


        $validatedData = $this->generateValidatedData($this->fields, $data);
        $validatedData['salt'] = "no-need";
        $sql = $this->insertSQL($validatedData);
        $this->connection->prepare($sql)->execute($validatedData);
        echo "user inserted";
    }

    function update($data)
    {
        // validation fails
        if(!$this->validateUser($data))
            return;


        $validatedData = $this->generateValidatedData($this->fields, $data);
        $validatedData['salt'] = "no-need";
        $validatedData['dt_modified'] = $this->current_date_mysql_timestamp();
        $sql = $this->updateSQL($validatedData);
        $validatedData['user_modified'] = $data['user_modified'];
        $validatedData['user_id'] = $data['user_id'];
        $this->connection->prepare($sql)->execute($validatedData);
        echo "user updated";
    }

    // sets disable to 1 rather than deleting
    function softDelete($data)
    {
        $validatedData = [
            'dt_modified' => $this->current_date_mysql_timestamp(),
            'user_modified' => $data['user_modified'],
            'user_id' => $data['user_id'],
            'disable' => 1
        ];
        $sql = "UPDATE users SET user_modified = :user_modified, dt_modified = :dt_modified, disable = :disable WHERE user_id = :user_id";
        $this->connection->prepare($sql)->execute($validatedData);
        echo "user soft deleted";
    } 

    // permanently deletes user
    function delete($user_id)
    {
        $sql = "DELETE FROM ".$table." WHERE id = ?"; 
        $stmt = $this->connection->prepare($sql);
        $response = $stmt->execute([$user_id]);
    }

    // return current date timestamp for mysql timestamp storing
    private function current_date_mysql_timestamp()
    {
        return date('Y-m-d H:i:s', time());
    }

    // create prepared statement sql for insert
    private function insertSQL($validatedData) 
    {
        $sql = "INSERT INTO users (";
        $values_sql = "";
        foreach($validatedData as $field => $value) {
            $sql .= $field;
            $values_sql .= ":$field";
            // only add comma if not last element
            end($validatedData);
            if($field !== key($validatedData)) {
                $sql .= ",";
                $values_sql .= ",";
            }
        }
        $sql .= ") VALUES ($values_sql)";
        return $sql;
    }

    // create prepared statement sql for update
    private function updateSQL($validatedData)
    {
        $sql = "UPDATE users SET ";
        foreach($validatedData as $field => $value) {
            $sql .= "$field = :$field, ";
        }
        $sql .= "user_modified = :user_modified WHERE user_id = :user_id";
        return $sql;
    }

    // use the schema of db for initial validation data i.e. if nullable allow it
    function validateUser($data)
    {
        if(!$this->validateRequired(['job_title'], $data) || !$this->validateDate(['date_of_birth'], $data)) {
            return false;
        }
        return true;
    }

    // Generates the data to be inserted or updated
    // only keeping !empty values, let mysql default the rest
    private function generateValidatedData($fields, $data)
    {
        $validatedData = [];
        foreach($fields as $field) {
            if(array_key_exists($field, $data) && !empty($data[$field])) {
                $validatedData[$field] = $data[$field];
            }
        }
        return $validatedData;
    }
    // BOOLEAN
    // $fields: assoc array of fields to validate
    // $data: $_POST data to validate
    // validate required data must be present and not empty
    private function validateRequired($fields, $data)
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $data) || empty($data[$field])) {
                // should be flashing error to session
                echo "Error: $field required";
                return false;
            }
        }
        return true;
    }

    // BOOLEAN
    // $fields: assoc array of fields
    // $data: $_POST data to validate
    // simple date time validation
    private function validateDate($fields, &$data)
    {
        foreach($fields as $field) {
            try {
                // only validate if not empty since we are not checking for "required" here
                // format the date
                if(array_key_exists($field, $data) && !empty($data[$field])) {
                    $data['date_of_birth'] = new DateTime($data[$field]);
                }
            } catch (Exception $e) {
                // should be flashing error to session
                echo "Invalid date for field $field";
                return false;
            }
        }
        return true;
    }

    // validateInteger
    // other validation functions
}
?>