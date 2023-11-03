<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_alertify;

/**
 * [Description alert]
 */
class alert {

    private $record;
    private $altered = false;

    /**
     * Constructs a 'block_alertify' instance with an optional alert record.
     *
     * This constructor initializes a 'block_alertify' instance, setting default values for the alert record's fields
     * such as 'cmid', 'template', 'enabled', and 'course'. If a record is provided, it updates the fields based on
     * the values from the given record.
     *
     * @param object|null $record (optional) An alert record to populate the 'block_alertify' instance.
     */
    public function __construct($record = null) {
        $this->record = (object) [
            'cmid' => 0,
            'template' => '',
            'enabled' => 0,
            'course' => 0,
        ];
        if (!is_null($record)) {
            foreach ($record as $key => $value) {
                $this->record->$key = $value;
            }
        }
    }

    /**
     * Magic method to retrieve the value of a field in the alert record.
     *
     * This magic method allows you to retrieve the value of a specific field from the alert record associated with the
     * current 'block_alertify' instance. It checks if the field exists in the record, and if it does, it returns its value;
     * otherwise, it returns null.
     *
     * @param string $name The name of the field to retrieve.
     *
     * @return mixed|null The value of the field if it exists; otherwise, null.
     */
    public function __get($name) {
        if (isset($this->record->$name)) {
            return $this->record->$name;
        }
        return null;
    }

    /**
     * Magic method to check if a field in the alert record is set (exists).
     *
     * This magic method checks if a specific field exists in the alert record associated with the current 'block_alertify'
     * instance. It returns true if the field exists and has a value, otherwise, it returns false.
     *
     * @param string $name The name of the field to check for existence.
     *
     * @return bool True if the field exists and is set, false otherwise.
     */
    public function __isset($name) {
        return isset($this->record->$name);
    }

    /**
     * Magic method for setting the values of alert record fields.
     *
     * This magic method allows for the setting of values in the alert record associated with the current 'block_alertify'
     * instance. It checks if the specified field exists in the record and updates the field's value. If the new value is
     * different from the old value, it marks the record as altered.
     *
     * @param string $name The name of the field to be set.
     * @param mixed $value The new value to assign to the field.
     */
    public function __set($name, $value) {
        if (!isset($this->record->$name)) {
            return;
        }
        if ($this->record->$name != $value) {
            $this->altered = true;
        }
        $this->record->$name = $value;
    }

    /**
     * Returns a copy of the alert record for form processing.
     *
     * This function creates a copy of the alert record associated with the current 'block_alertify' instance. It is
     * typically used for form processing, allowing you to work with a duplicate of the data without affecting the
     * original record.
     *
     * @return mixed A copy of the alert record for form processing.
     */
    public function data_for_form() {
        return clone $this->record;
    }

    /**
     * Deletes the alert record from the database.
     *
     * This function is responsible for removing the alert record associated with the current 'block_alertify' instance
     * from the 'block_alertify' database table. It uses the unique 'id' field of the alert record to perform the deletion.
     */
    public function delete() {
        global $DB;
        $DB->delete_records('block_alertify', ['id' => $this->record->id]);
    }

    /**
     * Creates an instance of the 'block_alertify' class for a specific ID.
     *
     * This static function is used to create an instance of the 'block_alertify' class based on a given ID. It queries
     * the database to retrieve the record with the specified ID from the 'block_alertify' table and uses it to instantiate
     * a new 'block_alertify' object.
     *
     * @param int $id The unique identifier for the alert record to be retrieved.
     *
     * @return self A new 'block_alertify' object initialized with the retrieved record data.
     *
     * @throws dml_exception If the record with the specified ID does not exist (MUST_EXIST).
     */
    public static function instance($id) {
        global $DB;
        $record = $DB->get_record('block_alertify', array('id' => $id), '*', MUST_EXIST);
        return new self($record);
    }

    /**
     * Saves or updates the alert record in the database.
     *
     * This function is responsible for saving the alert record to the 'block_alertify' database table.
     * If the alert is valid, it updates the 'timemodified' field and, depending on whether the record already
     * exists, inserts a new record or updates an existing one. It also handles tracking if the record has been altered.
     *
     * @return bool|int False if the alert is not valid or an update fails; otherwise, it returns true for no changes,
     *                 or the number of rows updated (usually 1) after a successful insert or update operation.
     */
    public function save() {
        global $DB;
        if (!$this->valid()) {
            return false;
        }
        $this->record->timemodified =  time();
        if (empty($this->record->id)) {
            $this->record->timecreated = $this->record->timemodified;
            $this->record->alertcreated = $this->record->timemodified;
            $this->record->id = $DB->insert_record('block_alertify', $this->record);
        }

        if (!$this->altered) {
            return true;
        }
        if ($update = $DB->update_record('block_alertify', $this->record)) {
            $this->altered = false;
        }
        return $update;
    }

    /**
     * Updates the timestamp of the alert to 4 days later and saves the changes to the database.
     *
     * This function takes the current timestamp of the alert's creation, adds four days to it, and updates
     * the 'alertcreated' field of the alert record. It then marks the alert as altered and saves the changes
     * to the database.
     *
     */
    public function set_sent_and_save() {
        /// Update the timestamp to 4 days later.
        $timestamp = $this->record->alertcreated; // Your original timestamp

        // Convert the timestamp to a DateTime object
        $date = new \DateTime("@$timestamp");

        // Add four days to the DateTime
        $date->modify('+4 days');

        // Get the updated timestamp
        $updatedTimestamp = $date->getTimestamp();

        $this->record->alertcreated = $updatedTimestamp;
        $this->altered = true;

        return $this->save();
    }

    /**
     * Checks the validity of the alert record.
     *
     * This function examines the alert record to determine its validity. If the 'course' field is empty,
     * the alert is considered invalid and the function returns false. Otherwise, it returns true to indicate
     * that the alert is valid.
     *
     * @return bool True if the alert is valid, false if it's not valid.
     */
    public function valid() {
        if (empty($this->record->course)) {
            return false;
        }
        return true;
    }
}
