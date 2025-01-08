<?php

namespace A2billing\Forms;

use A2billing\Logger;
use A2billing\Table;
use ADOConnection;
use Profiler_Console as Console;
use const PASSWORD_DEFAULT;

/***************************************************************************
 *
 * Class.FormHandler.php : FormHandler - PHP : Handle, Form Generator (FG) for A2Billing
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Billing solution for use with Asterisk(tm).
 * Copyright (C) 2004, 2009 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.a2billing.org for more information about
 * the A2Billing project.
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/
class FormHandler
{
    private static self $Instance;

    private array $_vars = [];

    /**
     * @var array basically just the contents of $_REQUEST
     */
    private array $_processed = [];

    public ADOConnection $DBHandle;

    /** @var bool if the current submission has passed all validation checks */
    public bool $all_fields_valid = true;

    /** @var bool|int The result of a non-select query (bool for update and delete, inserted ID for insert) */
    public $QUERY_RESULT = false;

    /* CONFIG THE VIEWER : CV */

    /** @var string Message to display if there's no data found for list view */
    public string $CV_NO_FIELDS = "THERE IS NO RECORD !";

    public string $CV_TITLE_TEXT = '';

    /** @var string[] Parameters to add to the URL of the list view sorting/pagination buttons */
    public array $CV_FOLLOWPARAMETERS = [];

    /** @var int debug level 0 (none) - 3 (lots) */
    public int $FG_DEBUG = 0;

    /** @var string The name of the element you are managing */
    public string $FG_INSTANCE_NAME = "";

    /** @var string The table name for queries */
    public string $FG_QUERY_TABLE_NAME = "";

    /** The primary key column of the table */
    public string $FG_QUERY_PRIMARY_KEY = 'id';

    /** @var array[] Tables to join to the query; ["t2" => ["t1.col", "=", "t2.col"]] gives "LEFT JOIN t2 ON (t1.col = t2.col)" */
    public array $query_table_joins;

    /** @var array list of columns from the SQL query to display in the list */
    public array $list_query_columns = [];

    /** @var array columns/values to be used as a condition in list queries */
    public array $list_query_conditions = [];

    /** @var array List of columns for the list display query to be grouped by */
    public array $list_query_group_columns = [];

    /** @var array List of columns for the list display query to be ordered by */
    public array $list_query_order_columns = [];

    /** @var string Direction (ASC or DESC) for the list display query ordering */
    public string $list_query_order_direction = "ASC";

    /** @var array Data used to build the list view table */
    public array $FG_LIST_TABLE_CELLS = [];

    /** @var bool Whether to place an add button in the list view's action column */
    public bool $FG_ENABLE_ADD_BUTTON = false;

    /** @var bool Whether to place a delete button in the list view's action column */
    public bool $FG_ENABLE_DELETE_BUTTON = false;
    /** @var string|null The link for the delete button */
    public ?string $FG_DELETE_BUTTON_LINK = null;
    /** @var string Code which is eval'd to decide whether to show the delete button */
    public string $FG_DELETE_BUTTON_CONDITION = '';
    /** @var array Primary key of entries that can't be deleted */
    public array $FG_DELETION_FORBIDDEN_ID = [];

    /** @var bool Whether to place an info button in the list view's action column */
    public bool $FG_ENABLE_INFO_BUTTON = false;
    /** @var string The link for the info button */
    public string $FG_INFO_BUTTON_LINK = '';

    /** @var bool Whether to place an edit button in the list view's action column */
    public bool $FG_ENABLE_EDIT_BUTTON = false;
    /** @var string|null The link for the edit button */
    public ?string $FG_EDIT_BUTTON_LINK = null;
    /** @var string Code which is eval'd to decide whether to show the edit button */
    public string $FG_EDIT_BUTTON_CONDITION = '';

    /** @var int Size of pages for the list view */
    public int $FG_LIST_VIEW_PAGE_SIZE = 10;
    /** @var int Number of pages in the current list view */
    public int $FG_LIST_VIEW_PAGE_COUNT = 0;
    /** @var int Number of rows in the current list view */
    public int $FG_LIST_VIEW_ROW_COUNT = 0;
    
    /** @var bool Whether to enable the list view filter form */
    public bool $FG_FILTER_ENABLE = false;
    /** @var string The column that will be checked for a matching value */
    public string $FG_FILTER_COLUMN = '';
    /** @var string Text used to label the form (prefixed by "Filter on ") */
    public string $FG_FILTER_LABEL = '';

    /** @var bool Whether to enable the second filter (only used in FG_var_did_billing.inc */
    public bool $FG_FILTER2_ENABLE = false;

    /** @var string The column that will be checked for a matching value */
    public string $FG_FILTER2_COLUMN = '';

    /** @var string Text used to label the form (prefixed by "Filter on ") */
    public string $FG_FILTER2_LABEL = '';

    /** @var bool Whether to show a search popup at the top of the list view */
    public bool $search_form_enabled = false;

    /** @var array List of elements to be added to the search form */
    public array $search_form_elements = [];

    /** @var string The text for the top of the search dialog */
    public string $search_form_title = "";

    /** @var string The session variable that stores search values */
    public string $search_session_key = '';

    /** @var bool Whether to enable a delete button on the search to allow user to remove all searched items */
    public bool $search_delete_enabled = true;

    /** @var bool Whether to enable a CSV export button at the bottom of a list view */
    public bool $FG_EXPORT_CSV = false;

    /** @var bool Whether to enable an XML export button at the bottom of a list view */
    public bool $FG_EXPORT_XML = false;

    /** @var string A session variable used to hold export info */
    public string $export_session_key = "export_data";

    /** @var array List of columns to use in the export */
    public array $FG_EXPORT_FIELD_LIST = [];

    /** @var array<array<string,string>> An array containing button definitions for the list entries */
    public array $list_action_buttons = [];

    /** @var string help text shown in all views (can be overridden for individual views) */
    public string $help_text = "";

    /** @var string help text for list view */
    public string $list_help_text = "";

    /** @var string help text for edit view */
    public string $edit_help_text = "";

    /** @var string help text for add view */
    public string $add_help_text = "";

    /** @var string help text for delete view */
    public string $del_help_text = "";

    //	-------------------- DATA FOR THE EDITION --------------------

    /**
     * @var array{array{
     *     type: string,
     *     label: string,
     *     table: Table,
     *     insert: string,
     *     foreign_key: string,
     *     section: string,
     *     validator: callable|null,
     *     validation_err: bool,
     *     attributes: array<string,mixed>
     * }} List of form elements used to create the edit form
     */
    public array $FG_EDIT_FORM_ELEMENTS = [];

    /** @var array A list of field names considered "splittable" during create or edit (values like e.g. 12-14 or 15;16;17) */
    public array $FG_SPLITABLE_FIELDS = [];

    /** @var array columns/values to be used as a condition in update/delete queries as well as fetching for edits */
    public array $update_query_conditions = ["id" => "%id"];

    /**
     * @var array list of key/value pairs that will be added to the edit form as hidden inputs
     * @todo only used in admin/Public/form_data/FG_var_card.inc; why not just use fg_edit_query_hidden_inputs?
     */
    public array $FG_EDIT_FORM_HIDDEN_INPUTS = [];

    /**
     * @var array list of key/value pairs that will be added to the add form as hidden inputs
     * @todo only used in customer/form_data/FG_var_signup.inc
     */
    public array $FG_ADD_FORM_HIDDEN_INPUTS = [];
    /** @var array list of key/value pairs that will be added to the edit form AND the SQL query */

    public array $FG_EDIT_QUERY_HIDDEN_INPUTS = [];

    /** @var array list of key/value pairs that will be added to the add form AND the SQL query */
    public array $FG_ADD_QUERY_HIDDEN_INPUTS = [];

    /**
     * @var array
     * @todo used only in FG_var_signup.inc, should figure a way to get rid of this
     */
    public array $REALTIME_SIP_IAX_INFO = [];

    /** @var string Where to redirect the user after adding a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_ADD;

    /** @var string Where to redirect the user after deleting a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_DELETE;

    /** @var string Where to redirect the user after editing a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_EDIT;

    /** @var string Message text for the edit page */
    public string $FG_INTRO_TEXT_EDITION = "You can modify, through the following form, the different properties of your #FG_INSTANCE_NAME#<br>";

    /** @var string Message text for the delete page */
    public string $FG_INTRO_TEXT_ASK_DELETION = "If you really want to remove this #FG_INSTANCE_NAME#, click on the delete button.";

    /** @var string Result text for the delete page */
    public string $FG_INTRO_TEXT_DELETION = "One #FG_INSTANCE_NAME# has been deleted!";

    /** @var string Message text for the add page */
    public string $FG_INTRO_TEXT_ADITION = "Add a \"#FG_INSTANCE_NAME#\" now.";

    /** @var string Result text for the add page */
    public string $FG_TEXT_ADITION_CONFIRMATION = "Your new #FG_INSTANCE_NAME# has been inserted.";

    /** @var string Error text for the add page */
    public string $FG_TEXT_ADITION_ERROR = 'Your new #FG_INSTANCE_NAME# has not been inserted.';

    /** @var string Error text for the add page */
    public string $FG_TEXT_ERROR_DUPLICATION = "You cannot choose more than one !";


    /** @var string Text telling you to click the button */
    public string $FG_ADD_PAGE_BOTTOM_TEXT = "Click 'Confirm Data' to continue";

    /** @var string Label for the "save" button when creating a new item */
    public string $FG_ADD_PAGE_SAVE_BUTTON_TEXT = 'Confirm Data';

    /** @var string Text telling you to click the button */
    public string $FG_EDIT_PAGE_BOTTOM_TEXT = "Click 'Confirm Data' to continue";

    /** @var callable|null Static method of FormBO class executed after creating */
    public $FG_ADDITIONAL_FUNCTION_AFTER_ADD = null;

    /** @var callable|null Static method of FormBO class executed before deleting */
    public $FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = null;

    /** @var callable|null Static method of FormBO class executed after deleting */
    public $FG_ADDITIONAL_FUNCTION_AFTER_DELETE = null;

    /** @var callable|null Static method of FormBO class executed before editing */
    public $FG_ADDITIONAL_FUNCTION_BEFORE_EDITION = null;

    /** @var callable|null Static method of FormBO class executed after editing */
    public $FG_ADDITIONAL_FUNCTION_AFTER_EDITION = null;

    /** @var bool not sure what this means, but I'm confident it would go away with proper foreign keys */
    public bool $FG_FK_DELETE_ALLOWED = false;

    // Foreign Key Tables
    public array $FG_FK_TABLENAMES = [];

    //Foreign Key Field Names
    public array $FG_FK_EDITION_CLAUSE = [];

    //Foreign Key Delete Message Display, it will display the confirm delete dialog if there is some
    //some detail table exists. depends on the values of FG_FK_DELETE_ALLOWED
    public bool $FG_FK_DELETE_CONFIRM = false;

    //Foreign Key Records Count
    public int $FG_FK_RECORDS_COUNT = 0;

    //Foreign Key Exists so Warn only not to delete ,,Boolean
    public bool $FG_FK_WARNONLY = false;

    // Delete Message for FK
    public string $FG_FK_DELETE_MESSAGE = "Are you sure to delete all records connected to this instance.";

    private bool $FG_ENABLE_LOG = ENABLE_LOG;

    /** @var string The CSRF token for the current request */
    public string $FG_CSRF_TOKEN;

    private bool $alarm_db_error_duplication = false;

    public bool $FG_LIST_ADDING_BUTTON1 = false;

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_LINK1 = "?form_action=ask-add";

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_ALT1 = "";

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_IMG1 = "";

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_MSG1 = "Add \"#FG_INSTANCE_NAME#\"";

    public bool $FG_LIST_ADDING_BUTTON2 = false;

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_LINK2;

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_ALT2;

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_IMG2;

    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_MSG2;

    /**
     * @param string $tablename the table name of the object we're working with
     * @param string $instance_name a label for the object
     * @param string $primary_key the primary key of the table (if joining tables, make sure it's unambiguous)
     * @param array<string,array<string>> $joins a list of joins formatted for use by Table::processJoinedTables()
     */
    public function __construct(string $tablename, string $instance_name, string $primary_key = "id", array $joins = [])
    {
        Console::log('Construct FormHandler');
        Console::logMemory($this, 'FormHandler Class : Line ' . __LINE__);
        Console::logSpeed('FormHandler Class : Line ' . __LINE__);
        self::$Instance = $this;
        $this->FG_QUERY_TABLE_NAME = $tablename;
        $this->FG_INSTANCE_NAME = $instance_name;
        $this->DBHandle = DbConnect();
        $this->FG_QUERY_PRIMARY_KEY = $primary_key;
        if ($primary_key !== "id") {
            $this->list_query_order_columns = [$primary_key];
            $this->update_query_conditions = [$primary_key => "%id"];
        }
        $this->query_table_joins = $joins;

        if (strtolower($_SERVER["REQUEST_METHOD"]) === "post") {
            $posted_token = $_POST["csrf_token"] ?? "";
            $session_token = $_SESSION['CSRF_TOKEN'] ?? "";

            // Check CSRF
            if ($session_token !== $posted_token) {
                echo "CSRF Error!";
                exit();
            } else {
                //Remove key from the session
                unset($_SESSION['CSRF_TOKEN']);
            }
        }
        if (empty($_REQUEST["popup_select"])) {
            // Initializing anti csrf token (Generate a key, concat it with salt and hash it)
            $this->FG_CSRF_TOKEN = hash('SHA256', CSRF_SALT . $this->genCsrfTokenKey());
            $_SESSION['CSRF_TOKEN'] = $this->FG_CSRF_TOKEN;
            if ($this->FG_DEBUG) {
                echo 'CSRF NEW TOKEN : ' . $this->FG_CSRF_TOKEN . '<br />';
            }
        }
        $this->_vars = array_merge($_GET, $_POST);

        //initializing variables with _
        $this->CV_NO_FIELDS = sprintf(_("No %s has been created"), $this->FG_INSTANCE_NAME);
        $this->CV_TITLE_TEXT = sprintf(_("%s list"), $this->FG_INSTANCE_NAME);
        $this->FG_INTRO_TEXT_EDITION = sprintf(_("Use this form to modify your %s."), $this->FG_INSTANCE_NAME);
        $this->FG_INTRO_TEXT_ASK_DELETION = sprintf(_("If you really want to remove this %s, click the delete button"), $this->FG_INSTANCE_NAME);
        $this->FG_INTRO_TEXT_DELETION = sprintf(_("One %s has been deleted"), $this->FG_INSTANCE_NAME);
        $this->FG_INTRO_TEXT_ADITION = sprintf(_("Add a %s now"), $this->FG_INSTANCE_NAME);
        $this->FG_LIST_ADDING_BUTTON_MSG1 = sprintf(_("Add %s"), $this->FG_INSTANCE_NAME);
        $this->FG_TEXT_ADITION_CONFIRMATION = sprintf(_("Your new %s has been inserted"), $this->FG_INSTANCE_NAME);
        $this->FG_TEXT_ADITION_ERROR = sprintf(_("Your new %s hasn't been inserted"), $this->FG_INSTANCE_NAME);

        $this->FG_TEXT_ERROR_DUPLICATION = _("You cannot choose more than one !");
        $this->search_form_title = _("Define the search criteria");
        $this->FG_ADD_PAGE_BOTTOM_TEXT = _("Click 'Confirm Data' to continue");
        $this->FG_EDIT_PAGE_BOTTOM_TEXT = _("Click 'Confirm Data' to continue");
        $this->FG_FK_DELETE_MESSAGE = _("Are you sure you want to delete all records connected to this instance?");

        /* only modified once in admin/FG_var_signup.inc */
        $this->FG_ADD_PAGE_SAVE_BUTTON_TEXT = _('Confirm Data');
    }

    /*
    * Generate a csrf token
    */
    private function genCsrfTokenKey(): string
    {
        $token1 = microtime();
        $token2 = uniqid("", true);
        $token3 = session_id();
        $token4 = mt_rand();

        return base64_encode($token1 . $token2 . $token3 . $token4);
    }

    public static function GetInstance(): FormHandler
    {
        return self::$Instance;
    }

    /**
     * Perform the execution of some actions to prepare the form generation
     *
     * @todo: why isn't this just done in the constructor instead of manually calling it 100 times?
     */
    public function init()
    {
        $this->_vars = array_merge($_GET, $_POST);

        $processed = $this->getProcessed();

        Console::log('FormHandler -> init');
        Console::logMemory(false, 'FormHandler -> init : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> init : Line ' . __LINE__);

        $qs = array_filter(
            ["current_page" => $processed["current_page"] ?? null, "order" => $processed["order"] ?? null, "sens" => $processed["sens"] ?? null],
            fn ($v) => !is_null($v)
        );
        $ext_link = "&amp;" . http_build_query($qs, "", "&amp;");
        $this->FG_EDIT_BUTTON_LINK ??= "?form_action=ask-edit" . $ext_link . "&amp;id=";
        $this->FG_DELETE_BUTTON_LINK ??= "?form_action=ask-delete" . $ext_link . "&amp;id=";
    }

    public function getProcessed(): array
    {
        foreach ($this->_vars as $key => $value) {
            if (str_contains($key, "^^")) {
                $this->_processed[$key] = $value;
                $key = str_replace("^^", ".", $key);
            }
            if (empty($this->_processed[$key])) {
                $this->_processed[$key] = $value;
                // this is hashing admin and agent passwords on save
                // todo: make this a property of the input component or something
                if ($key === "pwd_encoded" && !empty($value)) {
                    $this->_processed[$key] = password_hash($this->_processed[$key], PASSWORD_DEFAULT);
                }
            }
        }

        return $this->_processed;
    }

    // ----------------------------------------------
    // RECIPIENT METHODS
    // ----------------------------------------------

    /**
     * Add a plain value to the list table view
     *
     * @param string $label the table column header
     * @param string $field the database column name
     * @param callable|null $callback a function that is passed the value (or the provided arguments) before display
     * @param array $arguments if provided, arguments to the function
     *                          %[0-9]+ are replaced with the given row values
     *                          %X is always replaced with the current value
     * @param bool $sortable whether or not to allow sort
     * @return self
     */
    public function AddListValue(string $label, string $field, callable $callback = null, array $arguments = [], bool $sortable = true): self
    {
        if ($field) {
            $this->list_query_columns[] = $field;
        }
        $this->FG_LIST_TABLE_CELLS[] = [
            "type" => "",
            "header" => $label,
            "field" => $field,
            "function" => $callback,
            "arguments" => $arguments,
            "sortable" => $sortable,
        ];

        return $this;
    }

    /**
     * Adds a mapping to the list view table that translates a DB value to a pretty one
     *
     * @param string $label the table column header
     * @param string $field the database column name
     * @param array $map the mapping (key/value to raw/translated values)
     * @param bool $sortable whether or not to allow sort (note, will be done on raw value)
     * @return self
     */
    public function AddListMapping(string $label, string $field, array $map, bool $sortable = true): self
    {
        if ($field) {
            $this->list_query_columns[] = $field;
        }
        $this->FG_LIST_TABLE_CELLS[] = [
            "type" => "list",
            "header" => $label,
            "field" => $field,
            "options" => $map,
            "sortable" => $sortable,
        ];

        return $this;
    }

    /**
     * Adds a mapping to the list view table that does a lookup to translate a DB value to a pretty one
     *
     * @param string $label the table column header
     * @param string $field the database column name
     * @param Table $table a database object; first 2 columns will be used for raw and translated values
     * @param array $conditions an array of conditions to pass to $table->getRows()
     * @param string $url if provided, the cell will be a link to this URL with the first column value appended
     * @param bool $sortable whether or not to allow sort (note, will be done on raw value)
     * @return self
     */
    public function AddListSqlMapping(
        string $label,
        string $field,
        Table $table,
        array $conditions = [],
        string $url = "",
        bool $sortable = true
    ): self
    {
        if ($field) {
            $this->list_query_columns[] = $field;
        }
        $result = $table->getRows($this->DBHandle, $conditions);
        $map = array_combine(
            array_column($result, 0),
            array_column($result, 1)
        );

        $this->FG_LIST_TABLE_CELLS[] = [
            "type" => "list",
            "header" => $label,
            "field" => $field,
            "options" => $map,
            "sortable" => $sortable,
            "href" => $url,
        ];

        return $this;
    }

    /**
     * Add a column to the database query for use in callback
     * arguments (%n) or action button conditions (|coln|)
     *
     * @param string $field
     * @return $this
     */
    public function AddListHiddenValue(string $field): self
    {
        $this->list_query_columns[] = $field;

        return $this;
    }

    /**
     * Overrides query field names for the list view
     *
     * @param string|array $fields array or comma-separated list of column names used for list view
     * @return void
     * @todo figure out where this is required and work around it
     */
    public function FieldViewElement($fields): void
    {
        if (is_string($fields)) {
            $this->list_query_columns = array_map("trim", explode(",", $fields));
        } elseif (is_array($fields)) {
            $this->list_query_columns = $fields;
        }
        // instance_primary_key is used to fill in links for edit/delete buttons
        $this->list_query_columns[] = "$this->FG_QUERY_PRIMARY_KEY AS instance_primary_key";
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param callable<string>|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @param string $check_emptyvalue If set to "NO", empty values are not validated; if set to "NO-NULL" empty values are added to the SQL query as NULL
     * @param callable<string>|null $custom_function A callback to run the value through before displaying it
     * @return void
     */
    public function AddEditElement(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        array $html_attributes = [],
        ?callable $validator = null,
        string $error_message = "",
        string $section_name = "",
        string $check_emptyvalue = "",
        callable $custom_function = null // only used in FG_var_config.inc to convert 0/1 to yes/no
    )
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "type" => "INPUT",
            "attributes" => $html_attributes,
            "validator" => $validator,
            "error" => $error_message,
            "check_empty" => strtoupper($check_emptyvalue),
            "section_name" => $section_name,
            "custom_function" => $custom_function,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
    }

    /**
     * Add a split day/time field, used in admin/Public/form_data/FG_var_def_ratecard.inc
     */
    public function AddEditDayTime(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        string $default_value = "",
        string $error_message = "",
        string $section_name = ""
    )
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "type" => "DAYTIME",
            "error" => $error_message,
            "section_name" => $section_name,
            "comment" => $form_text_bottom,
            "validation_err" => true,
            "default" => $default_value,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param Table $table The table to check
     * @param array $conditions A condition to apply using a WHERE clause
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param array $first_option array containing a value and label (k/v) for the first options in the list
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @return void
     */
    public function AddEditSqlSelect(
        string $label_text,
        string $fieldname,
        Table $table,
        array $conditions = [],
        string $default_value = "",
        array $first_option = [],
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = ""
    ): void
    {
        $options = [];
        array_map(
            function ($v) use (&$options) {
                $options[$v[0]] = $v[1];
            },
            $table->getRows($this->DBHandle, $conditions)
        );

        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "SELECT",
            "attributes" => $html_attributes,
            "error" => $error_message,
            "select_type" => "LIST",
            "select_fields" => $options,
            "first_option" => $first_option,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param array $options An array of options to build the select element with
     * @param string|int $default_value When adding (not editing) the value of the selected item
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditSelect(
        string $label_text,
        string $fieldname,
        array  $options,
               $default_value = "",
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = "",
        string $section_name = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "SELECT",
            "attributes" => $html_attributes,
            "error" => $error_message,
            "select_type" => "LIST",
            "select_fields" => $options,
            "first_option" => [],
            "section_name" => $section_name,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param array $options An array of data (name, value) to build radio buttons
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the inputs
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditRadio(
        string $label_text,
        string $fieldname,
        array  $options,
        string $default_value = "",
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = "",
        string $section_name = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "RADIOBUTTON",
            "attributes" => $html_attributes,
            "error" => $error_message,
            "radio_options" => $options,
            "section_name" => $section_name,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $href The address of the popup
     * @param string $form_text_bottom Text to display below the form input
     * @param array $html_attributes HTML attributes for the input
     * @param callable<string>|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @return void
     */
    public function AddEditPopup(
        string $label_text,
        string $fieldname,
        string $href,
        string $form_text_bottom = "",
        array $html_attributes = [],
        ?callable $validator = null,
        string $error_message = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "popup_dest" => $href,
            "popup_params" => "width=750,height=450,top=50,left=100,scrollbars=1",
            "type" => "POPUPVALUE",
            "attributes" => $html_attributes,
            "validator" => $validator,
            "error" => $error_message,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
    }

    /**
     * Create a multi-part component to insert a record into a foreign table
     *
     * @param string $label The label text
     * @param Table $table the table to use for display of existing records
     * @param string $insert_column the text column to edit
     * @param string $foreign_key new records will be created with this column set to the object's PK
     * @param string $section_name If provided, added as a row above the input
     * @param callable|null $validator A callback to validate the value before saving it
     * @param bool $multiline Determines whether to use <input> or <textarea>
     * @param bool $select Determines whether to use a <select> element
     * @param Table|null $pivot_table If set, $table is only used for display; $pivot table is used for updates
     * @return void
     * @todo this function is only used in FG_var_card.inc
     */
    public function AddEditHasMany(
        string $label,
        Table $table,
        string $insert_column,
        string $foreign_key,
        string $section_name = "",
        ?callable $validator = null,
        bool $multiline = false,
        bool $select = false,
        ?Table $pivot_table = null
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "type" => "HAS_MANY",
            "label" => $label,
            "table" => $table,
            "insert" => $insert_column,
            "foreign_key" => $foreign_key,
            "section" => $section_name,
            "multiline" => $multiline,
            "select" => $select,
            "pivot_table" => $pivot_table,
            "validator" => $validator,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param array $html_attributes HTML attributes for the input
     * @param callable<string>|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditTextarea(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        array $html_attributes = [],
        ?callable $validator = null,
        string $error_message = "",
        string $section_name = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "name" => $fieldname,
            "type" => "TEXTAREA",
            "label" => $label_text,
            "section" => $section_name,
            "attributes" => $html_attributes,
            "comment" => $form_text_bottom,
            "validator" => $validator,
            "error" => $error_message,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label the label for the input
     * @param string $fieldname the name of the database column, also used for HTML element names
     * @param bool $relative if true, this is an "x months ago" type input
     * @param bool $recent if true, this is a "in the last x days" type input
     * @return void
     */
    public function AddSearchDateInput(string $label, string $fieldname, bool $relative = false, bool $recent = false) {
        $column = $fieldname;
        $fieldname = str_replace(".", "^^", $fieldname);
        $inputnames = ["{$fieldname}_start", "{$fieldname}_end"];
        if ($relative) {
            unset($inputnames[0]);
            $inputnames[1] .= "_months";
        }
        $this->search_form_elements[] = [
            "label" => $label,
            "input" => $inputnames,
            "operator" => ["{$fieldname}_start_type", "{$fieldname}_end_type"],
            "column" => $column,
            "type" => "DATE",
            "relative" => $relative,
            "recent" => $recent,
        ];
    }

    /**
     * Sets Search form fieldnames for the view module
     *
     * @public
     * @ $displayname , $fieldname, $fieldvar
     */
    public function AddSearchTextInput($displayname, $fieldname)
    {
        $fieldname = str_replace(".", "^^", $fieldname);
        $fieldvar = $fieldname . "type";
        $this->search_form_elements[] = [
            "label" => $displayname,
            "input" => [$fieldname],
            "operator" => [$fieldvar],
            "type" => "TEXT",
        ];
    }

    public function AddSearchComparisonInput($displayname, $fieldname1, $fieldname2, $sqlfield)
    {
        $fieldname1 = str_replace(".", "^^", $fieldname1);
        $fieldname2 = str_replace(".", "^^", $fieldname2);
        $fieldvar1 = $fieldname1 . "type";
        $fieldvar2 = $fieldname2 . "type";
        $this->search_form_elements[] = [
            "label" => $displayname,
            "input" => [$fieldname1, $fieldname2],
            "operator" => [$fieldvar1, $fieldvar2],
            "column" => $sqlfield,
            "type" => "COMPARISON",
        ];
    }

    /**
     * Add a SELECT element to a search form based on a database lookup
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param Table $table a database object; first 2 columns will be used for value and content
     * @param string $order column to order by
     * @param string $direction direction asc or desc
     * @param array $conditions any conditions to apply to the query
     * @return void
     */
    public function AddSearchSqlSelectInput(
        string $label,
        string $name,
        Table $table,
        string $order = "",
        string $direction = "ASC",
        array $conditions = []
    )
    {
        $name = str_replace(".", "^^", $name);
        $sqlorder = [];
        if ($order) {
            // todo: let this default to second column
            // will need to work on Table to get a column array available
            $sqlorder = [$order];
        }
        $options = [];
        array_map(
            function ($v) use (&$options) {
                $options[$v[0]] = $v[1];
            },
            $table->getRows($this->DBHandle, $conditions, $sqlorder, $direction)
        );

        $this->search_form_elements[] = [
            "label" => $label,
            "input" => [$name],
            "options" => $options,
            "process" => true,
            "type" => "SELECT",
        ];
    }

    /**
     * Add a SELECT element to a search form
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param array $options options for the element; key is used for value attribute, value used for content
     * @param bool $process if false, this element will NOT be used in the database query
     * @return void
     */
    public function AddSearchSelectInput(string $label, string $name, array $options = [], bool $process = true)
    {
        $name = str_replace(".", "^^", $name);
        $this->search_form_elements[] = [
            "label" => $label,
            "input" => [$name],
            "options" => $options,
            "process" => $process,
            "type" => "SELECT",
        ];
    }

    /**
     * Add a radio button to a search form
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param array $options options for the element; key is used for value attribute, value used for content
     * @param bool $process if false, this element will NOT be used in the database query
     * @return void
     */
    public function AddSearchRadioInput(string $label, string $name, array $options = [], bool $process = true)
    {
        $name = str_replace(".", "^^", $name);
        $this->search_form_elements[] = [
            "label" => $label,
            "input" => [$name],
            "options" => $options,
            "process" => $process,
            "type" => "RADIO",
        ];
    }

    public function AddSearchPopupInput(string $label, string $name, string $href, int $select = 1): void
    {
        $name = str_replace(".", "^^", $name);
        $this->search_form_elements[] = [
            "label" => $label,
            "input" => [$name],
            "href" => $href,
            "select" => $select,
            "type" => "POPUP",
        ];
    }

    public function AddSearchButton(string $label, string $name, string $value = '1', string $class = 'btn-secondary', string $onclick = ''): void
    {
        $name = str_replace(".", "^^", $name);
        $this->search_form_elements[] = [
            "input" => [$name],
            "value" => $value,
            "label" => $label,
            "class" => $class,
            "onclick" => $onclick,
            "type" => "BUTTON",
        ];
    }

    /**
     * Add an action button to the last column of the list view table
     * When conditionally adding buttons, if $match_index is numeric
     * the comparison will be done against the outputted text in the table
     * column. Otherwise the comparison will be done against the database
     * column value.
     *
     * @param string $url the destination link
     * @param string $label the text (used as alt if image supplied)
     * @param string $image an image URL
     * @param string $class an HTML class to add to the button
     * @param string $match_index a column of the database results to compare against
     * @param string $match_value the value to match (see note above)
     * @return void
     */
    public function AddListActionButton(
        string $url,
        string $label,
        string $image,
        string $class = "",
        string $match_index = "",
        string $match_value = ""
    ): void
    {
        $this->list_action_buttons[] = compact(
            "url",
            "label",
            "image",
            "class",
            "match_index",
            "match_value"
        );
    }

    /**
     * Add a "<SELECT>" button for use with popups. This REMOVES any existing button configurations
     *
     * @param string $url
     * @param string $label if not specified, defaults to "select"
     * @return void
     */
    public function AddListSelectActionButton(string $url = "#", string $label = "") {
        $this->list_action_buttons = [[
            "url" => $url,
            "label" => $label ?: _("select"),
            "image" => "",
            "class" => "popup-select-button",
            "match_index" => "",
            "match_value" => "",
        ]];
    }

    /**
     * Adds to the conditions a comparison between a column and a posted value
     *
     * @param string $left_column the column name
     * @param string $operator post field name of the operator: 1=eq 2=lte 3=lt 4=gt 5=gte
     * @param string $post_field post field name for the comparison
     * @return void
     */
    public function do_field_duration(string $left_column, string $operator, string $post_field): void
    {
        $processed = $this->getProcessed();

        if (!isset($processed[$post_field]) || $processed[$post_field] === "") {
            return;
        }

        $val = $processed[$post_field];
        switch ($processed[$operator] ?? null) {
            default:
                $this->list_query_conditions[] = ["SUB", [$left_column => $val]];
                break;
            case 2:
                $this->list_query_conditions[] = ["SUB", [$left_column => ["<=", $val]]];
                break;
            case 3:
                $this->list_query_conditions[] = ["SUB", [$left_column => ["<", $val]]];
                break;
            case 4:
                $this->list_query_conditions[] = ["SUB", [$left_column => [">", $val]]];
                break;
            case 5:
                $this->list_query_conditions[] = ["SUB", [$left_column => [">=", $val]]];
                break;
        }
    }

    /**
     * Adds to the conditions a comparison between a column and a posted value
     *
     * @param string $column the column name, also used for the post field name
     * @param string|null $like_type post field name of the operator: 1=equal (default) 2=starts with 3=contains 4=ends with
     * @return void
     */
    public function do_field(string $column, string $like_type = null): void
    {
        $processed = $this->getProcessed();

        if (!isset($processed[$column]) || $processed[$column] === "") {
            return;
        }

        $op = $processed[$like_type] ?? 1;
        $val = $processed[$column];

        $LIKE = "LIKE";
        if (DB_TYPE === "postgres") {
            $LIKE = "ILIKE";
        }

        switch ($op ?? null) {
            case 1:
                $this->list_query_conditions[$column] = $val;
                break;
            case 2:
                $this->list_query_conditions[$column] = [$LIKE, "$val%"];
                break;
            default:
                $this->list_query_conditions[$column] = [$LIKE, "%$val%"];
                break;
            case 4:
                $this->list_query_conditions[$column] = [$LIKE, "%$val"];
                break;
        }
    }

    /**
     * Function to execture the appropriate action
     *
     * @public
     */
    public function perform_action(string &$form_action): array
    {
        //security check
        $self = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        switch ($form_action) {
            case "ask-add":
            case "add":
                if (!$this->FG_ENABLE_ADD_BUTTON) {
                    header("Location: $self");
                    die();
                }
                break;
            case "ask-edit":
            case "edit":
                if (!$this->FG_ENABLE_EDIT_BUTTON) {
                    header("Location: $self");
                    die();
                }
                break;
            case "ask-del-confirm":
            case "ask-delete":
            case "delete":
                if (!$this->FG_ENABLE_DELETE_BUTTON) {
                    header("Location: $self");
                    die();
                }
                break;
        }

        $processed = $this->getProcessed();
        if (!empty($processed["id"])) {
            $this->update_query_conditions = array_map(
                fn ($v) => str_replace("%id", $processed["id"], $v),
                $this->update_query_conditions
            );
        }

        switch ($form_action) {
            case "add":
                $this->perform_add($form_action);
                break;
            case "edit":
                $this->perform_edit($form_action);
                break;
            case "delete":
                $this->perform_delete();
                break;
        }

        $processed = $this->getProcessed();

        if ($form_action === "ask-delete" && in_array($processed['id'], $this->FG_DELETION_FORBIDDEN_ID)) {
            $this->gotoLocation($this->FG_LOCATION_AFTER_DELETE ?? $self);
        }

        $list = [];
        if (
            $form_action === "list" || $form_action === "edit" || $form_action === "ask-delete" ||
            $form_action === "ask-edit" || $form_action === "add-content" || $form_action === "del-content" ||
            $form_action === "ask-del-confirm"
        ) {
            if (!empty($processed["order"])) {
                $this->list_query_order_columns = array_filter([$processed['order']]);
            }
            if (in_array(strtolower($processed["sens"] ?? ""), ["asc", "desc"])) {
                $this->list_query_order_direction = $processed["sens"];
            }

            $current_page = (int)($processed["current_page"] ?? 0);

            $session_limit = $this->FG_QUERY_TABLE_NAME . "-displaylimit";
            if (array_key_exists($session_limit, $_SESSION) && (int)$_SESSION[$session_limit]) {
                $this->FG_LIST_VIEW_PAGE_SIZE = (int)$_SESSION[$session_limit];
            }

            if (!empty($processed['mydisplaylimit'])) {
                if ($processed['mydisplaylimit'] === 'ALL') {
                    $this->FG_LIST_VIEW_PAGE_SIZE = 5000;
                } elseif ((int)$processed['mydisplaylimit'] > 0) {
                    $this->FG_LIST_VIEW_PAGE_SIZE = (int)$processed['mydisplaylimit'];
                }
                $_SESSION[$this->FG_QUERY_TABLE_NAME . "-displaylimit"] = $this->FG_LIST_VIEW_PAGE_SIZE;
            }

            if ($form_action === "list") {
                if (!in_array("$this->FG_QUERY_PRIMARY_KEY AS instance_primary_key", $this->list_query_columns)) {
                    // instance_primary_key is used to fill in links for edit/delete buttons
                    $this->list_query_columns[] = "$this->FG_QUERY_PRIMARY_KEY AS instance_primary_key";
                }

                $this->prepare_list_subselection($form_action);

                // Code here to call the Delete Selected items Fucntion
                if (isset($processed['deleteselected'])) {
                    $this->Delete_Selected();
                }

                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $this->list_query_columns, $this->query_table_joins);
                $list = $instance_table->getRows(
                    $this->DBHandle,
                    $this->list_query_conditions,
                    $this->list_query_order_columns,
                    $this->list_query_order_direction,
                    $this->list_query_group_columns,
                    $this->FG_LIST_VIEW_PAGE_SIZE,
                    $current_page * $this->FG_LIST_VIEW_PAGE_SIZE
                );

                $this->FG_LIST_VIEW_ROW_COUNT = $instance_table->countRows($this->DBHandle, $this->list_query_conditions, $this->list_query_group_columns);

                if ($this->FG_LIST_VIEW_ROW_COUNT <= $this->FG_LIST_VIEW_PAGE_SIZE) {
                    $this->FG_LIST_VIEW_PAGE_COUNT = 1;
                } else {
                    $this->FG_LIST_VIEW_PAGE_COUNT = ceil($this->FG_LIST_VIEW_ROW_COUNT / $this->FG_LIST_VIEW_PAGE_SIZE);
                }
            } else {
                //todo: when is this code run and why?
                $cols = array_column($this->FG_EDIT_FORM_ELEMENTS, "name");
                $fields = implode(",", $cols);

                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $fields, $this->query_table_joins);
                $list = $instance_table->getRows($this->DBHandle, $this->update_query_conditions);

                //PATCH TO CLEAN THE IMPORT OF PASSWORD FROM THE DATABASE
                $index = array_search("pwd_encoded", $cols);
                if ($index !== false) {
                    $list[0][$index] = "";
                    $list[0]["pwd_encoded"] = "";
                }
            }
        }

        return $list;
    }

    /**
     * Function to prepare the clause from the session filter
     *
     * @public
     */
    public function prepare_list_subselection($form_action): void
    {
        $processed = $this->getProcessed();

        if ($form_action !== "list" || (!$this->search_form_enabled && !$this->FG_FILTER_ENABLE)) {
            return;
        }

        if ($processed['cancelsearch'] ?? false) {
            $_SESSION[$this->search_session_key] = '';
        }

        if ($this->FG_FILTER_ENABLE) {
            $filtercolumn = $this->FG_FILTER_COLUMN;
            $filterprefix = $processed["filterprefix"] ?? "";
            if ($filtercolumn && $filterprefix) {
                $this->list_query_conditions[$filtercolumn] = ["LIKE", "$filterprefix%"];
            }
        }

        if ($this->FG_FILTER2_ENABLE) {
            $filtercolumn = $this->FG_FILTER2_COLUMN;
            $filterprefix = $processed["filterprefix2"];
            if ($filtercolumn && $filterprefix) {
                $this->list_query_conditions[$filtercolumn] = ["LIKE", "$filterprefix%"];
            }
        }

        // RETRIEVE THE CONTENT OF THE SEARCH SESSION AND
        if (($processed['posted_search'] ?? 0) != 1 && !empty($_SESSION[$this->search_session_key])) {
            $element_arr = json_decode($_SESSION[$this->search_session_key], true);
            foreach ($element_arr as $entity_name => $entity_value) {
                $this->_processed[$entity_name] = $entity_value;
                $processed[$entity_name] = $entity_value;
                $_POST[$entity_name] = $entity_value;
                $processed['posted_search'] = 1;
            }
        }

        if (($processed['posted_search'] ?? 0) != 1) {
            return;
        }

        $search = [];

        foreach ($this->search_form_elements as $el) {
            if (($el["process"] ?? true) === false) {
                continue;
            }
            foreach ($el["input"] as $i => $input) {
                $input = str_replace("^^", ".", $input);
                $search[$input] = $processed[$input];
                if (!empty($el["operator"][$i])) {
                    $search[$el["operator"][$i]] = $processed[$el["operator"][$i]];
                }
                if ($el["type"] === "TEXT") {
                    $this->do_field($input, $el["operator"][$i]);
                } elseif ($el["type"] === "COMPARISON" || ($el["type"] === "DATE" && !empty($processed["enable_$input"]))) {
                    $this->do_field_duration($el["column"], $el["operator"][$i], $input);
                } elseif ($el["type"] === "SELECT" || $el["type"] === "SQL_SELECT" || $el["type"] === "POPUP") {
                    $this->do_field($input);
                }
            }
        }

        $_SESSION[$this->search_session_key] = json_encode(array_filter($search, fn($v) => is_string($v) && strlen($v)));
    }

    /****************************************
     * Function to delete all pre selected records,
     * This Function Gets the selected records and delete them from DB
     ******************************************/
    public function Delete_Selected()
    {
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, ["*"], $this->query_table_joins);
        $instance_table->deleteRow($this->DBHandle, $this->list_query_conditions);
    }

    /**
     * Function to perform the add action after inserting all data in required fields
     */
    public function perform_add(string &$form_action): void
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->all_fields_valid = true;
        $values = [];
        $arr_value_to_import = [];
        // ignore the joins since we're doing an insert
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME);

        foreach ($this->FG_EDIT_FORM_ELEMENTS as &$row) {
            $field = $row["name"];
            $attr = $row["attributes"];
            if (array_key_exists("disabled", $attr)) {
                continue;
            }

            if (array_key_exists("multiple", $attr) && is_array($processed[$field])) {
                $values[$field] = (int)array_sum($processed[$field]);
            }
            if (!empty($row["validator"])) {
                if ($processed[$field] === "" && str_starts_with($row["check_empty"] ?? "", "NO")) {
                    $row["validation_err"] = true;
                } else {
                    $result = call_user_func($row["validator"], $processed[$field]);
                    $row["validation_err"] = $result;
                    if ($result !== true) {
                        $this->all_fields_valid = false;
                        $form_action = "ask-add";
                        continue;
                    }
                }
            }
            // CHECK IF THIS IS A SPLITABLE FIELD LIKE 012-014 OR 15,16,17
            if (in_array($field, $this->FG_SPLITABLE_FIELDS)) {
                $value = $processed[$field];
                if (empty($value) || str_starts_with($value, "_")) {
                    // dialprefix can be a range *or* an Asterisk-style extension pattern starting with _
                    continue;
                }
                $arr_value_to_import[$field] = $this->split_ranges($value);
                $values[$field] = "%check_array%";
            } elseif (!empty($processed[$field]) && $row["type"] !== "CAPTCHAIMAGE") {
                $values[$field] = $processed[$field];
            }
        } // endforeach with reference
        unset ($row);

        if ($this->all_fields_valid === false) {
            $this->QUERY_RESULT = false;
            return;
        }

        foreach ($this->FG_ADD_QUERY_HIDDEN_INPUTS as $name => $value) {
            $values[$name] = $value;
        }

        if (($key = array_search("%check_array%", $values)) !== false) {
            foreach ($arr_value_to_import[$key] as $array_value) {
                $values[$key] = $array_value;
                $result = $instance_table->addRow(
                    $this->DBHandle,
                    $values,
                    $this->FG_QUERY_PRIMARY_KEY,
                    $id
                );
                // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
                if ($result && is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                    ($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)($id);
                }
            }
        } else {
            $result = $instance_table->addRow(
                $this->DBHandle,
                $values,
                $this->FG_QUERY_PRIMARY_KEY,
                $id
            );
            // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
            if ($result && is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                ($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)($id);
            }
        }
        $this->QUERY_RESULT = $id ?? false;

        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    $_SESSION["admin_id"],
                    2,
                    sprintf(_("New %s created"), $this->FG_INSTANCE_NAME),
                    _("User added a new record in database"),
                    $this->FG_QUERY_TABLE_NAME,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI'],
                    array_keys($values),
                    array_values($values)
                );
            }
            $this->gotoLocation($this->FG_LOCATION_AFTER_ADD ?? "?form_action=ask-edit&id=");
        }
    }


    /**
     * Function to edit the fields
     *
     * @public
     */
    public function perform_edit(&$form_action)
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->all_fields_valid = true;
        $values = [];
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->query_table_joins);

        foreach ($this->FG_EDIT_FORM_ELEMENTS as &$row) {
            $field = $row["name"] ?? "";
            $attr = $row["attributes"] ?? [];
            if (empty($field) || array_key_exists("disabled", $attr)) {
                continue;
            }

            if (array_key_exists("multiple", $attr) && is_array($processed[$field])) {
                $values[$field] = (int)array_sum($processed[$field]);
            }
            if (!empty($row["validator"])) {
                if ($processed[$field] === "" && str_starts_with($row["check_empty"] ?? "", "NO")) {
                    $row["validation_err"] = true;
                } else {
                    $result = call_user_func($row["validator"], $processed[$field]);
                    $row["validation_err"] = $result;
                    if ($result !== true) {
                        $this->all_fields_valid = false;
                        $form_action = "ask-edit";
                        continue;
                    }
                }
            }
            if ($processed[$field] === "" && ($row["check_empty"] ?? "") === "NO-NULL") {
                $values[$field] = null;
            } else {
                $values[$field] = $processed[$field];
            }
        } // end foreach with reference
        unset($row);

        if ($this->all_fields_valid === false) {
            $this->QUERY_RESULT = false;
            return;
        }

        foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value) {
            $values[$name] = $value;
        }

        if (is_callable($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION)) {
            ($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION)($processed["id"]);
        }

        $this->QUERY_RESULT = $instance_table->updateRow(
            $this->DBHandle,
            $values,
            $this->update_query_conditions
        );

        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    $_SESSION["admin_id"],
                    3,
                    sprintf(_("Existing %s updated"), $this->FG_INSTANCE_NAME),
                    _("User edited a record in database"),
                    $this->FG_QUERY_TABLE_NAME,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI'],
                    array_keys($values),
                    array_values($values)
                );
            }

            // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
            if (is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION)) {
                ($this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION)($processed["id"]);
            }

            $this->gotoLocation($this->FG_LOCATION_AFTER_EDIT ?? "?form_action=list");
        }
    }


    /**
     * Function to delete a record
     *
     * @public
     */
    public function perform_delete()
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->all_fields_valid = true;

        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);

        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->query_table_joins);
        if ($tableCount === $clauseCount && $clauseCount > 0 && $this->FG_FK_DELETE_ALLOWED && !empty($processed['id'])) {
            $instance_table->setDeleteFk($this->FG_FK_TABLENAMES, $this->FG_FK_EDITION_CLAUSE, $processed["id"], $this->FG_FK_WARNONLY);
        }
        $instance_table->FK_DELETE = !$this->FG_FK_WARNONLY;

        $this->QUERY_RESULT = $instance_table->deleteRow($this->DBHandle, $this->update_query_conditions);
        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    $_SESSION["admin_id"],
                    3,
                    "A " . strtoupper($this->FG_INSTANCE_NAME) . " DELETED",
                    "A RECORD IS DELETED, EDITION CLAUSE USED IS " . array_kv($this->update_query_conditions),
                    $this->FG_QUERY_TABLE_NAME,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI']
                );
            }
            if (is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE)) {
                ($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE)($processed["id"]);
            }

            $this->gotoLocation($this->FG_LOCATION_AFTER_DELETE ?? "?form_action=list");
        } else {
            echo _("error deletion");
        }
    }

    private function gotoLocation(string $location): void
    {
        if (empty($location)) {
            return;
        }
        $processed = $this->getProcessed();
        $params = [
            "current_page" => $processed["current_page"] ?? 0,
            "order" => $processed["order"] ?? "",
            "sens" => $processed["sens"] ?? "",
        ];
        $qs = (str_contains($location, "?") ? "&" : "?")
            . http_build_query(array_filter($params));
        if (str_ends_with($location, "id=")) {
            $location .= urlencode($processed["id"]);
        }
        header("Location: $location$qs");
        die();
    }

    /**
     * Checks for child records, populates FormHandler::$FG_FK_RECORDS_COUNT
     *
     * @return bool whether there are any child records
     */
    public function check_child_records(): bool
    {
        $processed = $this->getProcessed();
        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);
        if (empty($this->FG_FK_TABLENAMES) || empty($processed["id"]) || $tableCount !== $clauseCount) {
            return false;
        }
        $rowcount = 0;
        foreach ($this->FG_FK_TABLENAMES as $i => $table) {
            $instance_table = new Table($table);
            $rowcount += $instance_table->countRows($this->DBHandle, [$this->FG_FK_EDITION_CLAUSE[$i] => $processed['id']]);
        }
        $this->FG_FK_RECORDS_COUNT = $rowcount;
        if ($this->FG_DEBUG == 1) {
            echo "<br>$this->FG_FK_RECORDS_COUNT children found";
        }

        return ($rowcount > 0);
    }

    /**
     * Add content from HasMany and custom SQL selects (only used in FG_var_[tariffgroup|agent|service|card].inc)
     *
     * @var int $index the index within $this->FG_EDIT_FORM_ELEMENTS
     * @var int $id the id of the object to be used as foreign key
     */
    public function perform_add_content(int $index, int $id)
    {
        $entry = $this->FG_EDIT_FORM_ELEMENTS[$index];
        if (empty($entry["table"])) {
            return;
        }
        $processed = $this->getProcessed();
        /** @var Table $table */
        $table = $entry["pivot_table"] ?? $entry["table"];
        $column = $entry["insert"];
        $value = $processed["add-content-value"];
        if (is_callable($entry["validator"] ?? null)) {
            $result = call_user_func($entry["validator"], $value);
            if ($result !== true) {
                $this->all_fields_valid = false;
                $this->FG_EDIT_FORM_ELEMENTS[$index]["validation_err"] = $result;

                return;
            }
        }
        $foreign_key = $entry["foreign_key"];
        $table->addRow($this->DBHandle, [$column => $value, $foreign_key => $id]);
    }


    /**
     * Delete content from SQL selects (only used in FG_var_[tariffgroup|agent|service].inc)
     *
     * @var int $index the index within $this->FG_EDIT_FORM_ELEMENTS
     * @var int $id the id of the object to be used as foreign key
     */
    public function perform_del_content(int $index, int $id)
    {
        $entry = $this->FG_EDIT_FORM_ELEMENTS[$index];
        if (empty($entry["table"])) {
            return;
        }

        $processed = $this->getProcessed();
        /** @var Table $table */
        if (!empty($entry["pivot_table"])) {
            $table = $entry["pivot_table"];
            $column = $entry["insert"];
        } else {
            $table = $entry["table"];
            $column = $table->fields[0];
        }
        $value = $processed["del-content-value"];
        $foreign_key = $entry["foreign_key"];
        $table->deleteRow($this->DBHandle, [$column => $value, $foreign_key => $id]);
    }


    /**
     * Function to create the top page section
     * todo: return, don't echo
     */
    public function create_toppage(string $form_action): void
    {
        $help = "";
        $msg = "";
        if ($form_action === "ask-edit" || $form_action === "edit" || $form_action === "add-content" || $form_action === "del-content") {
            if ($form_action === "ask-edit") {
                $help = $this->edit_help_text ?: $this->help_text;
            }
            if ($this->alarm_db_error_duplication) {
                $msg = "<p class=\"danger\">$this->FG_TEXT_ERROR_DUPLICATION</p>";
            } else {
                $msg = $this->FG_INTRO_TEXT_EDITION;
            }
        } elseif ($form_action === "ask-add") {
            $msg = $this->FG_INTRO_TEXT_ADITION;
            $help = $this->add_help_text ?: $this->help_text;
        } elseif ($form_action === "ask-delete") {
            $help = $this->del_help_text ?: $this->help_text;
        } elseif ($form_action === "list") {
            $help = $this->list_help_text ?: $this->help_text;
        }

        if ($help) {
            $help = "<div class='row pb-3 align-items-center'><div class='col'>$help</div></div>";
        }
        if ($msg) {
            $msg = "<div class='row pb-3 align-items-center'><div class='col'>$msg</div></div>";
        }

        echo $help . $msg;
    }

    /**
     * CREATE_ACTIONFINISH : Function to display result
     * I think the only time this is used is if there is a database error when adding from A2B_entity_friend.php ???
     * @public
     */
    public function create_actionfinish($form_action)
    {
        if ($form_action === "delete") {
            $msg1 = "$this->FG_INSTANCE_NAME " . _("Deletion");
            $msg2 = $this->FG_INTRO_TEXT_DELETION;
        } elseif ($form_action === "add") {
            $msg1 = _("Insert New ") . $this->FG_INSTANCE_NAME;
            $msg2 = empty($this->QUERY_RESULT) ? "<span class='danger'>$this->FG_TEXT_ADITION_ERROR</span>" : $this->FG_INTRO_TEXT_ADITION;
        } else {
            return;
        }
        $html = "<div class='row pb-3'><div class='col'><p>$msg1</p><p>$msg2</p></div></div>";
        echo $html;
    }

    /**
     *  CREATE_CUSTOM : Function to display a custom message using form_action
     *
     * @public        TODO : maybe is better to allow use a string as parameter
     */
    public function create_custom($form_action)
    {
        $msg = "$form_action " . _("Done");
        $html = "<div class='row pb-3 align-items-center'><div class='col'><strong>$msg</strong></div></div>";
        echo $html;
    }

    public function create_search_form(bool $full_modal = false, bool $with_hide_button = true)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();

        echo new SearchForm($this, $processed, $full_modal, $with_hide_button);
    }

    public function create_search_button(string $content = null): string
    {
        $processed = $this->getProcessed();
        $class = "btn-outline-primary";
        $title = sprintf(_("Search %s"), $this->FG_INSTANCE_NAME);
        if (!empty($processed["posted_search"]) || !empty($_SESSION[$this->search_session_key])) {
            $class = "btn-primary btn-search-active";
            $title .= " (" . _("search activated") . ")";
        }
        $content = htmlspecialchars($content ?? sprintf(_("Search %s"), $this->FG_INSTANCE_NAME));

        return <<< HTML
        <button class="btn btn-sm $class" data-bs-toggle="modal" data-bs-target="#searchModal" title="$title">$content</button>

        HTML;
    }

    /**
     * Function to create the form
     *
     * @todo make this return a string, not echo
     */
    public function create_form(string $form_action, array $list)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();

        // passed by javascript functions for add-content and del-content
        $edit_form_index = $processed["form_el_index"] ?? 0;

        switch ($form_action) {
            case "add-content":
                $this->perform_add_content($edit_form_index, $processed['id']);
                echo new EditForm($this, $processed, $list);
                break;

            case "del-content":
                $this->perform_del_content($edit_form_index, $processed['id']);
                echo new EditForm($this, $processed, $list);
                break;

            case "ask-edit":
            case "edit":
                echo new EditForm($this, $processed, $list);
                break;

            case "ask-add":
                echo new AddForm($this, $processed, $list);
                break;

            case "ask-delete":
            case "ask-del-confirm":
                if (is_callable($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE)) {
                    // this function can insert a warning into the page top before delete is done
                    ($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE)($processed[$this->FG_QUERY_PRIMARY_KEY]);
                }
                if ($form_action === "ask-delete") {
                    $this->check_child_records();
                }

                echo new DeleteForm($this, $processed, $list, $form_action);
                break;

            case "list":
                echo new ViewForm($this, $processed, $list);
                break;

            case "delete":
            case "add":
                $this->create_actionfinish($form_action);
                break;

            default:
                $this->create_custom($form_action);
                break;
        }
    }

    /**
     * Do multi-page navigation.  Displays the prev, next and page options.
     *
     * @param int $page the page currently viewed (one-based, unlike URL parameter which is zero-based)
     * @param int $pages the total number of pages
     * @param string $url the url to refer to with the page number inserted
     * @param int $max_width the number of pages to make available at any one time (default = 10)
     */
    public static function printPages(int $page, int $pages, string $url, int $max_width = 10): string
    {
        // the number of pages on either side of the current page
        $window = intdiv($max_width, 2);

        if ($page < 0 || $page > $pages || $pages <= 1 || $max_width <= 0) {
            return "";
        }

        $url = rawurldecode($url);
        $prevlabel = _("Previous");
        $prevlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $page - 2, $url),
            $prevlabel
        );
        $prevdis = "";
        $firstlabel = _("First");
        $firstlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", 0, $url),
            $firstlabel
        );
        if ($page === 1) {
            $prevdis = "disabled";
            $prevlink = sprintf("<span class='page-link'>%s</span>", $prevlabel);
            $firstlink = sprintf("<span class='page-link'>%s</span>", $firstlabel);
        }

        $lastlabel = _("Last");
        $lastlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $pages - 1, $url),
            $lastlabel
        );
        $nextlabel = _("Next");
        $nextlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $page, $url),
            $nextlabel
        );
        $nextdis = "";
        if ($page >= $pages) {
            $nextdis = "disabled";
            $lastlink = sprintf("<span class='page-link'>%s</span>", $lastlabel);
            $nextlink = sprintf("<span class='page-link'>%s</span>", $nextlabel);
        }

        $ret = <<< HTML
        <nav aria-label="page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item $prevdis">
                    $firstlink
                </li>
                <li class="page-item $prevdis">
                    $prevlink
                </li>

        HTML;

        if ($page < $window * 2) {
            // |1 2 [3] 4 5...
            $min_page = 1;
            $max_page = min(2 * $window, $pages);
        } elseif ($pages >= $page + $window) {
            // ...4 5 [6] 7 8...
            $min_page = $page - $window;
            $max_page = $page + $window;
        } else {
            // ...6 7 [8] 9 10|
            $min_page = ($page - (2 * $window - ($pages - $page)));
            $max_page = $pages;
        }

        // Make sure min_page is always at least 1
        // and max_page is never greater than $pages
        $min_page = max($min_page, 1);
        $max_page = min($max_page, $pages);

        for ($i = $min_page; $i <= $max_page; $i++) {
            $link = str_replace("%s", $i - 1, $url);
            $aria = $act = "";
            if ($i === $page) {
                $act = "active";
                $aria = 'aria-current="page"';
            }
            $ret .= <<< HTML
                    <li class="page-item $act" $aria><a class="page-link" href="$link">$i</a></li>

            HTML;
        }
        $ret .= <<< HTML
                <li class="page-item $nextdis">
                    $nextlink
                </li>
                <li class="page-item $nextdis">
                    $lastlink
                </li>
            </ul>
        </nav>

        HTML;

        return $ret;
    }

    public function csrf_inputs(): string
    {
        if (!empty($this->FG_CSRF_TOKEN)) {
            return "<input type='hidden' name='csrf_token' value='$this->FG_CSRF_TOKEN'/>\n";
        }

        return "";
    }

    public function set_debug(int $level = 1): void
    {
        $this->FG_DEBUG = $level;
    }

    public function setup_export(
        ?array $columns = null,
        ?string $table = null,
        ?array $conditions = null,
        ?array $group = null,
        ?array $order = null,
        ?string $direction = null
    ): void
    {
        $columns ??= $this->FG_EXPORT_FIELD_LIST;
        $table ??= $this->FG_QUERY_TABLE_NAME;
        $conditions ??= $this->list_query_conditions;
        $group ??= $this->list_query_group_columns;
        $order ??= $this->list_query_order_columns;
        $direction ??= $this->list_query_order_direction;

        $_SESSION[$this->export_session_key] = [$columns, $table, $conditions, $group, $order, $direction];
    }

    /**
     * Take a string such as "3-5,12,18-20" and return ["3", "4", "5", "12", "18", "19", "20"]
     *
     * @param string $input
     * @return numeric-string[]
     */
    private function split_ranges(string $input): array
    {
        $array = [];
        $items = explode(",", $input);
        foreach ($items as $item) {
            $item = trim($item);
            $range = explode("-", $item, 2);
            if (isset($range[1])) {
                $min = trim($range[0]);
                $max = trim($range[1]);
                // get common prefix to avoid issues with very large numeric strings like card numbers
                $prefix_len = strspn("$min" ^ "$max", chr(0));
                $prefix = substr($min, 0, $prefix_len);
                $min = substr($min, $prefix_len);
                $max = substr($max, $prefix_len);
                if (is_numeric($min) && is_numeric($max) && $min < $max) {
                    for ($i = $min; $i <= $max; $i++) {
                        $array[] = $prefix . $i;
                    }
                } elseif (is_numeric($min)) {
                    $array[] = $prefix . $min;
                } elseif (is_numeric($max)) {
                    $array[] = $prefix . $max;
                }
            } elseif (is_numeric($range[0])) {
                $array[] = $range[0];
            }
        }

        return $array;
    }
}
