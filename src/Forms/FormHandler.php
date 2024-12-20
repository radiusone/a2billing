<?php

namespace A2billing\Forms;

use A2billing\Logger;
use A2billing\Table;
use ADOConnection;
use Closure;
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

    private array $_processed = [];

    public ADOConnection $DBHandle;

    /** @var bool ??? */
    public bool $VALID_SQL_REG_EXP = true;

    /** @var bool|int The result of a non-select query (bool for update and delete, inserted ID for insert) */
    public $QUERY_RESULT = false;

    /* CONFIG THE VIEWER : CV */

    /** @var string Message to display if there's no data found for list view */
    public string $CV_NO_FIELDS = "THERE IS NO RECORD !";

    public string $CV_TITLE_TEXT = '';

    /** @var string Parameters to add to the URL of the list view sorting/pagination buttons */
    public string $CV_FOLLOWPARAMETERS = '';

    /**
     * @var int Tracks the current page for pagination and DB queries
     * @todo this is barely used; could it be replaced with a variable?
     */
    public int $CV_CURRENT_PAGE = 0;

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
    public array $FG_QUERY_COLUMN_LIST = [];

    /** @var array columns/values to be used as a condition in list queries */
    public array $list_query_conditions = [];

    /** @var array List of columns for the list display query to be grouped by */
    public array $FG_QUERY_GROUPBY_COLUMNS = [];
    /** @var array List of columns for the list display query to be ordered by */
    public array $FG_QUERY_ORDERBY_COLUMNS = [];
    /** @var string|null Direction (ASC or DESC) for the list display query ordering */
    public ?string $FG_QUERY_DIRECTION = '';
    /** @var string Default sort order */
    public string $FG_TABLE_DEFAULT_ORDER = "id";
    /** @var string Default sort direction */
    public string $FG_TABLE_DEFAULT_SENS = "ASC";


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

    /** @var array List of form elements used to create the edit form */
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

    /** @var string Static method of FormBO class executed after creating */
    public string $FG_ADDITIONAL_FUNCTION_AFTER_ADD = '';

    /**
     * @var string Static method of FormBO class executed before deleting
     * @todo only used in admin/Public/form_data/FG_var_did.inc
     */
    public string $FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = '';

    /** @var string Static method of FormBO class executed after deleting */
    public string $FG_ADDITIONAL_FUNCTION_AFTER_DELETE = '';

    /** @var string Static method of FormBO class executed before editing */
    public string $FG_ADDITIONAL_FUNCTION_BEFORE_EDITION = '';

    /** @var string Static method of FormBO class executed after editing */
    public string $FG_ADDITIONAL_FUNCTION_AFTER_EDITION = '';

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
            $this->FG_TABLE_DEFAULT_ORDER = $primary_key;
            $this->update_query_conditions = [$primary_key => "%id"];
        }
        $this->query_table_joins = $joins;

        if (!empty($_POST)) {
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

        $this->def_list();

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
     * @public
     */
    public function init()
    {
        $this->_vars = array_merge($_GET, $_POST);

        $processed = $this->getProcessed();

        Console::log('FormHandler -> init');
        Console::logMemory(false, 'FormHandler -> init : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> init : Line ' . __LINE__);

        if (!empty($processed['section'])) {
            $section = $processed['section'];
            $_SESSION["menu_section"] = intval($section);
        }
        $ext_link = "&amp;" . http_build_query(["current_page" => $processed["current_page"] ?? "", "order" => $processed["order"] ?? "", "sens" => $processed["sens"] ?? ""], "", "&amp;");
        $this->FG_EDIT_BUTTON_LINK ??= "?form_action=ask-edit" . $ext_link . "&amp;id=";
        $this->FG_DELETE_BUTTON_LINK ??= "?form_action=ask-delete" . $ext_link . "&amp;id=";
    }

    /**
     * Define the list
     *
     * @public
     */
    public function def_list()
    {
        Console::log('FormHandler -> def_list');
        Console::logMemory($this, 'FormHandler -> def_list : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> def_list : Line ' . __LINE__);
    }

    public function &getProcessed(): array
    {
        foreach ($this->_vars as $key => $value) {
            if (str_contains($key, "^^")) {
                $this->_processed[$key] = sanitize_data($value);
            }
            $key = str_replace("^^", ".", $key);
            if (empty($this->_processed[$key])) {
                $this->_processed[$key] = sanitize_data($value);
                if ($key === "username") {
                    //rebuild the search parameter to filter character to format card number
                    $filtered_char = [" ", "-", "_", "(", ")", "+"];
                    $this->_processed[$key] = str_replace($filtered_char, "", $this->_processed[$key]);
                }
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
     * Sets query field names for the View module
     * Some items sent to AddViewElement have fake names, so this overrides them?
     *
     * @param string|array $fields array or comma-separated list of column names used for list view
     * @return void
     * @todo figure out where this is required and work around it
     */
    public function FieldViewElement($fields): void
    {
        if (is_string($fields)) {
            $this->FG_QUERY_COLUMN_LIST = array_map("trim", explode(",", $fields));
        } elseif (is_array($fields)) {
            $this->FG_QUERY_COLUMN_LIST = $fields;
        }
        // instance_primary_key is used to fill in links for edit/delete buttons
        $this->FG_QUERY_COLUMN_LIST[] = "$this->FG_QUERY_PRIMARY_KEY AS instance_primary_key";
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param string $html_attributes HTML attributes for the input
     * @param int|null $regexpr_nb A validation method number
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @param string $check_emptyvalue If set to "NO", empty values are not validated; if set to "NO-NULL" empty values are added to the SQL query as NULL
     * @param Closure|string $custom_function A callback to run the value through before displaying it
     * @param bool $field_enabled If set to false, the input will not be added
     * @return void
     */
    public function AddEditElement(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        string $html_attributes = "",
        ?int   $regexpr_nb = null,
        string $error_message = "",
        string $section_name = "",
        string $check_emptyvalue = "",
               $custom_function = "",
        bool   $field_enabled = true // only used in FG_var_signup.inc for captcha
    )
    {
        if (!$field_enabled) {
            return;
        }
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text, // 0
            "name" => $fieldname, // 1
            "type" => "INPUT", // 3
            "attributes" => $html_attributes, // 4
            "regex" => $regexpr_nb, // 5
            "error" => $error_message, // 6
            "check_empty" => strtoupper($check_emptyvalue), // 13
            "section_name" => $section_name, // 16
            "custom_function" => $custom_function, //15
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
     * @param string $sql_table The table to check
     * @param string $sql_column The columns to retrieve
     * @param string $sql_where A condition to apply using a WHERE clause
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param array $first_option 2 element array containing a value and label for the first option in the list
     * @param string $display_format A format string like "%1" which will be replaced with the first result column
     * @param string $form_text_bottom Text to display below the form input
     * @param string $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param array $custom_query If provided, an array containing values to build a custom query
     * @return void
     * @todo $custom_query is only used in FG_var_[agent|service|tariffgroup].inc
     */
    public function AddEditSqlSelect(
        string $label_text,
        string $fieldname,
        string $sql_table,
        string $sql_column,
        string $sql_where = "",
        string $default_value = "",
        array $first_option = [],
        string $display_format = "%1",
        string $form_text_bottom = "",
        string $html_attributes = "",
        string $error_message = "",
        array  $custom_query = []
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "SELECT",
            "attributes" => $html_attributes,
            "regex" => null,
            "error" => $error_message,
            "select_type" => "SQL",
            "sql_table" => $sql_table,
            "sql_field" => $sql_column,
            "sql_clause" => $sql_where,
            "select_format" => $display_format,
            "custom_query" => $custom_query,
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
     * @param string $html_attributes HTML attributes for the input
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
        string $html_attributes = "",
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
            "regex" => null,
            "error" => $error_message,
            "select_type" => "LIST",
            "select_fields" => $options,
            "select_format" => "",
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
     * @param string $html_attributes HTML attributes for the inputs
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
        string $html_attributes = "",
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
            "regex" => null,
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
     * @param string $html_attributes HTML attributes for the input
     * @param callable<string>|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @return void
     */
    public function AddEditPopup(
        string $label_text,
        string $fieldname,
        string $href,
        string $form_text_bottom = "",
        string $html_attributes = "",
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
     * @param string $label_text The label text
     * @param array $query_data Data used to build the query for populating items
     * @param bool $multiline Determines whether to use <input> or <textarea>
     * @param string $section_name If provided, added as a row above the input
     * @return void
     * @todo this function is only used in FG_var_card.inc
     */
    public function AddEditHasMany(
        string $label_text,
        array  $query_data,
        bool   $multiline = false,
        string $section_name = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = [
            "type" => "HAS_MANY",
            "label" => $label_text,
            "custom_query" => $query_data,
            "section" => $section_name,
            "multiline" => $multiline,
            "regex" => null,
            "validation_err" => true,
        ];
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param string $html_attributes HTML attributes for the input
     * @param callable<string>|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditTextarea(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        string $html_attributes = "",
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
     * Sets Search form select rows for the view module
     *
     * @public
     * @ $displayname , SQL or array to fill select and the name of select box
     */
    public function AddSearchSqlSelectInput(string $displayname, string $table, string $fields, string $clause,
                                                   $order, $sens, $select_name)
    {
        $select_name = str_replace(".", "^^", $select_name);
        $this->search_form_elements[] = [
            "label" => $displayname,
            "table" => $table,
            "columns" => $fields,
            "where" => $clause,
            "order" => $order,
            "dir" => $sens,
            "input" => [$select_name],
            "type" => "SQL_SELECT",
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
     * @param $rule_number
     * @param string $value
     * @return bool|string
     */
    private function validate_field($rule_number, string $value)
    {
        $messages = [
            _("(at least 3 characters)"),
            _("(must match email structure. Example : name@domain.com)"),
            _("(at least 5 successive characters appear at the end of this string)"),
            _("(at least 4 characters)"),
            _("(number format)"),
            _("(YYYY-MM-DD)"),
            _("(only number with more that 8 digits)"),
            _("(at least 8 digits using . or - or the space key)"),
            _("network adress format"),
            _("at least 1 character"),
            _("(YYYY-MM-DD HH:MM:SS)"),
            _("(AT LEAST 2 CARACTERS)"),
            _("(NUMBER FORMAT WITH/WITHOUT DECIMAL, use '.' for decimal)"),
            _("(NUMBER FORMAT OR 'defaultprefix' OR ASTERISK/POSIX REGEX FORMAT)"),
            _("(NUMBER FORMAT OR 'all')"),
            _("(HH:MM)"),
            _("You must write something."),
            _("8 characters alphanumeric"),
            _("Phone Number format"),
            _("(at least 6 Alphanumeric characters)"),
            _("(HH:MM:SS)"),
            _("(PERCENT FORMAT WITH/WITHOUT DECIMAL, use '.' for decimal and don't use '%' character. e.g.: 12.4 )"),
            "default" => _("A validation error occurred."),
        ];

        $result = null;
        switch ($rule_number) {
            case 0: $result = strlen($value) >= 3; break;
            case 1: $result = (bool)filter_var($value, FILTER_VALIDATE_EMAIL); break;
            case 2: $pattern = "/(.)\\1{4}$/"; break;
            case 3: $result = strlen($value) >= 4; break;
            case 4: $pattern = "/^[0-9]+$/"; break;
            case 5: $pattern = "/^(?:20|19)[0-9]{2}([- \\/.])(?:0[1-9]|1[012])\\1(?:0[1-9]|[12][0-9]|3[01])$/"; break;
            case 6: $pattern = "/^[0-9]{8,}$/"; break;
            case 7: $pattern = "/^[0-9][0-9. \\/-]{6,}[0-9]$/"; break;
            case 8: $result = strlen($value) >= 5; break;
            case 9: $result = strlen($value) >= 1; break;
            case 10: $pattern = "/^(?:20|19)[0-9]{2}([- \\/.])(?:0[1-9]|1[012])\\1(?:0[1-9]|[12][0-9]|3[01]) (?:[01][0-9]|2[0-3])(?::[0-5][0-9]){2}$/"; break;
            case 11: $result = strlen($value) >= 2; break;
            case 12: $pattern = "/^-?[0-9]+(\\.?[0-9]+)?$/"; break;
            // case 13: regex pattern ^(defaultprefix|[-,0-9]+|_[-[.[.][.].]0-9XZN(){}|.,_]+)$ was not valid, presumably this isn't used
            case 14: $pattern = "/^all|[0-9]+$/"; break;
            case 15: $pattern = "/^[0-9]{2}:[0-9]{2}$/"; break; // is this a duration or should time check be proper?
            case 16: $result = strlen($value) >= 15; break;
            case 17: $result = strlen($value) >= 8; break;
            case 18: $pattern = "/^\\+[0-9]+$/"; break;
            case 19: $pattern = "/^$_SESSION[captcha_code]$/"; break;
            case 20: $pattern = "/^[0-9]{2}(?::[0-9]{2}){2}$/"; break;  // is this a duration or should time check be proper?
            case 21: $result = $value >= 0 && $value <= 100; break;
            default: return $messages["default"];
        }
        if (is_null($result) && isset($pattern)) {
            $result = (bool)preg_match($pattern, $value);
        }

        return $result ?: $messages[$rule_number];
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
            if (!empty($this->FG_LOCATION_AFTER_DELETE)) {
                header("Location: " . $this->FG_LOCATION_AFTER_DELETE . $processed['id']);
            } else {
                header("Location: $self");
            }
            die();
        }

        $list = [];
        if (
            $form_action === "list" || $form_action === "edit" || $form_action === "ask-delete" ||
            $form_action === "ask-edit" || $form_action === "add-content" || $form_action === "del-content" ||
            $form_action === "ask-del-confirm"
        ) {
            if (!empty($processed["order"])) {
                $this->FG_QUERY_ORDERBY_COLUMNS = array_filter([$processed['order']]);
            }
            if (in_array(strtolower($processed["sens"] ?? ""), ["asc", "desc"])) {
                $this->FG_QUERY_DIRECTION = $processed['sens'];
            }
            $this->CV_CURRENT_PAGE = (int)($processed['current_page'] ?? 0);

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

            if (empty($this->FG_QUERY_ORDERBY_COLUMNS)) {
                $this->FG_QUERY_ORDERBY_COLUMNS = array_filter([$this->FG_TABLE_DEFAULT_ORDER]);
            }
            if (empty($this->FG_QUERY_DIRECTION)) {
                $this->FG_QUERY_DIRECTION = $this->FG_TABLE_DEFAULT_SENS;
            }

            if ($form_action === "list") {

                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $this->FG_QUERY_COLUMN_LIST, $this->query_table_joins);

                if ($this->FG_DEBUG) {
                    $params = [];
                    echo "<pre>";
                    echo json_encode($this->list_query_conditions, JSON_PRETTY_PRINT) . "\n";
                    echo "WHERE " . $instance_table->processWhereClauseArray($this->list_query_conditions, $params) . "\n";
                    echo json_encode($params, JSON_PRETTY_PRINT);
                    echo "</pre>";
                }
                $this->prepare_list_subselection($form_action);

                // Code here to call the Delete Selected items Fucntion
                if (isset($processed['deleteselected'])) {
                    $this->Delete_Selected();
                }

                if ($this->FG_DEBUG >= 2) {
                    echo "FG_ORDER = " . $this->FG_QUERY_ORDERBY_COLUMNS[0] . "<br>";
                    echo "FG_SENS = " . $this->FG_QUERY_DIRECTION . "<br>";
                    echo "FG_LIMITE_DISPLAY = " . $this->FG_LIST_VIEW_PAGE_SIZE . "<br>";
                    echo "CV_CURRENT_PAGE = " . $this->CV_CURRENT_PAGE . "<br>";
                }

                $list = $instance_table->getRows(
                    $this->DBHandle,
                    $this->list_query_conditions,
                    $this->FG_QUERY_ORDERBY_COLUMNS,
                    $this->FG_QUERY_DIRECTION,
                    $this->FG_QUERY_GROUPBY_COLUMNS,
                    $this->FG_LIST_VIEW_PAGE_SIZE,
                    $this->CV_CURRENT_PAGE * $this->FG_LIST_VIEW_PAGE_SIZE
                );
                if ($this->FG_DEBUG === 3) {
                    echo "<br>Clause : " . json_encode($this->list_query_conditions);
                }
                $this->FG_LIST_VIEW_ROW_COUNT = $instance_table->countRows($this->DBHandle, $this->list_query_conditions, $this->FG_QUERY_GROUPBY_COLUMNS);

                if ($this->FG_DEBUG >= 1) {
                    var_dump($list);
                }

                if ($this->FG_LIST_VIEW_ROW_COUNT <= $this->FG_LIST_VIEW_PAGE_SIZE) {
                    $this->FG_LIST_VIEW_PAGE_COUNT = 1;
                } else {
                    $this->FG_LIST_VIEW_PAGE_COUNT = ceil($this->FG_LIST_VIEW_ROW_COUNT / $this->FG_LIST_VIEW_PAGE_SIZE);
                }

                if ($this->FG_DEBUG === 3) {
                    echo "<br>Nb_record : " . $this->FG_LIST_VIEW_ROW_COUNT;
                    echo "<br>Nb_record_max : " . $this->FG_LIST_VIEW_PAGE_COUNT;
                }

            } else {
                $selected_elements = array_filter($this->FG_EDIT_FORM_ELEMENTS, fn ($v) => empty($v["custom_query"]));
                $cols = array_column($selected_elements, "name");
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

            if ($this->FG_DEBUG >= 2) {
                print_r($list);
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
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $this->FG_QUERY_COLUMN_LIST, $this->query_table_joins);
        $instance_table->deleteRow($this->DBHandle, $this->list_query_conditions);
    }

    /**
     * Function to perform the add action after inserting all data in required fields
     */
    public function perform_add(string &$form_action): void
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;
        $values = [];
        $arr_value_to_import = [];
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->query_table_joins);

        foreach ($this->FG_EDIT_FORM_ELEMENTS as &$row) {
            if (empty($row["custom_query"])) {
                $fields_name = $row["name"];
                $regexp = $row["regex"] ?? null;

                if (str_contains($row["attributes"], "multiple") && is_array($processed[$fields_name])) {
                    $total_mult_select = (int)array_sum($processed[$fields_name]);
                    $values[$fields_name] = $total_mult_select;
                } else {
                    // CHECK ACCORDING TO THE REGULAR EXPRESSION DEFINED
                    if (is_numeric($regexp) && !(str_starts_with($row["check_empty"] ?? "", "NO") && $processed[$fields_name] === "")) {
                        $row["validation_err"] = $this->validate_field($regexp, $processed[$fields_name]);
                        if ($row["validation_err"] !== true) {
                            $this->VALID_SQL_REG_EXP = false;
                            if ($this->FG_DEBUG == 1) {
                                echo "<br>-> $fields_name) Error Match";
                            }
                            $form_action = "ask-add";
                        }
                    } elseif ($regexp === "check_select" && $processed[$fields_name] == -1) {
                        // FOR SELECT FIELD WE HAVE THE check_select THAT WILL ENSURE WE DEFINE A VALUE FOR THE SELECTABLE FIELD
                        $row["validation_err"] = _("Validation error");
                        $this->VALID_SQL_REG_EXP = false;
                        $form_action = "ask-add";
                    } elseif (!empty($row["validator"])) {
                        if ($processed[$fields_name] === "" && str_starts_with($row["check_empty"] ?? "", "NO")) {
                            $result = true;
                        } else {
                            $result = call_user_func($row["validator"], $processed[$fields_name]);
                        }
                        if ($result !== true) {
                            $this->VALID_SQL_REG_EXP = false;
                            $form_action = "ask-add";
                        }
                        $row["validation_err"] = $result;
                    }
                    // CHECK IF THIS IS A SPLITABLE FIELD LIKE 012-014 OR 15,16,17
                    if (in_array($fields_name, $this->FG_SPLITABLE_FIELDS) && !str_starts_with($processed[$fields_name], '_')) {
                        $value = $processed[$fields_name];
                        $arr_value_to_import[$fields_name] = [];
                        $items = explode(",", $value);
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
                                        $arr_value_to_import[$fields_name][] = $prefix . $i;
                                    }
                                } elseif (is_numeric($min)) {
                                    $arr_value_to_import[$fields_name][] = $prefix . $min;
                                } elseif (is_numeric($max)) {
                                    $arr_value_to_import[$fields_name][] = $prefix . $max;
                                }
                            } else {
                                $arr_value_to_import[$fields_name][] = $range[0];
                            }
                        }

                        if (!empty($processed[$fields_name]) && !str_contains($row["attributes"], "disabled")) {
                            $values[$fields_name] = "%check_array%";
                        }
                    } elseif (!empty($processed[$fields_name]) && !str_contains($row["attributes"], "disabled") && $row["type"] !== "CAPTCHAIMAGE") {
                        $values[$fields_name] = $processed[$fields_name];
                    }
                }
            }
        } // endforeach with reference
        unset ($row);

        foreach ($this->FG_ADD_QUERY_HIDDEN_INPUTS as $name => $value) {
            $values[$name] = $value;
        }

        if ($this->VALID_SQL_REG_EXP === false) {
            $this->QUERY_RESULT = false;
        } elseif (($key = array_search("%check_array%", $values)) !== false) {
            foreach ($arr_value_to_import[$key] as $array_value) {
                $values[$key] = $array_value;
                $instance_table->addRow(
                    $this->DBHandle,
                    $values,
                    $this->FG_QUERY_PRIMARY_KEY,
                    $id
                );
                // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
                if (method_exists(FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                    call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD]);
                }
            }
        } else {
            $instance_table->addRow(
                $this->DBHandle,
                $values,
                $this->FG_QUERY_PRIMARY_KEY,
                $id
            );
            // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
            if (method_exists(FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD]);
            }
        }
        if (!empty($id)) {
            $this->QUERY_RESULT = $id;
        }

        if ($this->FG_ENABLE_LOG) {
            Logger::insertLog(
                $_SESSION["admin_id"],
                2,
                "NEW " . strtoupper($this->FG_INSTANCE_NAME) . " CREATED",
                "User added a new record in database",
                $this->FG_QUERY_TABLE_NAME,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_URI'],
                array_keys($values),
                array_values($values)
            );
        }
        if (!empty($id) && ($this->VALID_SQL_REG_EXP) && (isset($this->FG_LOCATION_AFTER_ADD))) {
            header("Location: " . $this->FG_LOCATION_AFTER_ADD . $id);
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
        $this->VALID_SQL_REG_EXP = true;
        $values = [];
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->query_table_joins);

        foreach ($this->FG_EDIT_FORM_ELEMENTS as $i => &$row) {
            if (empty($row["custom_query"])) {
                $fields_name = $row["name"];
                $regexp = $row["regex"];

                if (str_contains($row["attributes"], "multiple") && is_array($processed[$fields_name])) {
                    $total_mult_select = (int)array_sum($processed[$fields_name]);
                    $values[$fields_name] = $total_mult_select;
                } else {
                    if (is_numeric($regexp) && !(str_starts_with($row["check_empty"] ?? "", "NO") && ($processed[$fields_name] ?? null) === "")) {
                        $row["validation_err"] = $this->validate_field($regexp, $processed[$fields_name]);
                        if ($row["validation_err"] !== true) {
                            $this->VALID_SQL_REG_EXP = false;
                            if ($this->FG_DEBUG == 1) {
                                echo "<br>-> $i) Error Match";
                            }
                            $form_action = "ask-edit";
                        }
                    }
                    if (empty($processed[$fields_name]) && str_ends_with($row["check_empty"] ?? "", "NULL")) {
                        $values[$fields_name] = null;
                    } elseif ($row["type"] !== "SPAN") {
                        $values[$fields_name] = $processed[$fields_name];
                    }
                }
            }
        } // end foreach with reference
        unset($row);

        foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value) {
            $values[$name] = $value;
        }

        if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION]);
        }

        if ($this->VALID_SQL_REG_EXP) {
            $this->QUERY_RESULT = $instance_table->updateRow(
                $this->DBHandle,
                $values,
                $this->update_query_conditions
            );
        }

        if ($this->FG_ENABLE_LOG) {
            Logger::insertLog(
                $_SESSION["admin_id"],
                3,
                "A " . strtoupper($this->FG_INSTANCE_NAME) . " UPDATED",
                "A RECORD IS UPDATED, EDITION CALUSE USED IS " . array_kv($this->update_query_conditions),
                $this->FG_QUERY_TABLE_NAME,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_URI'],
                array_keys($values),
                array_values($values)
            );
        }

        // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION]);
        }

        if ($this->VALID_SQL_REG_EXP && !empty($this->FG_LOCATION_AFTER_EDIT)) {
            $ext_link = '';
            if (is_numeric($processed['current_page'])) {
                $ext_link .= "&current_page=" . $processed['current_page'];
            }
            if (!empty($processed['order']) && !empty($processed['sens'])) {
                $ext_link .= "&order=" . $processed['order'] . "&sens=" . $processed['sens'];
            }
            header("Location: " . $this->FG_LOCATION_AFTER_EDIT . $processed['id'] . $ext_link);
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
        $this->VALID_SQL_REG_EXP = true;

        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);

        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->query_table_joins);
        if ($tableCount === $clauseCount && $clauseCount > 0 && $this->FG_FK_DELETE_ALLOWED && !empty($processed['id'])) {
            $instance_table->setDeleteFk($this->FG_FK_TABLENAMES, $this->FG_FK_EDITION_CLAUSE, $processed["id"], $this->FG_FK_WARNONLY);
        }
        $instance_table->FK_DELETE = !$this->FG_FK_WARNONLY;

        $this->QUERY_RESULT = $instance_table->deleteRow($this->DBHandle, $this->update_query_conditions);
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
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE) > 0) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE]);
        }
        if (!$this->QUERY_RESULT) {
            echo _("error deletion");
        }

        if (!empty($this->FG_LOCATION_AFTER_DELETE)) {
            if ($this->FG_DEBUG == 1) {
                echo "<br> GOTO ; " . $this->FG_LOCATION_AFTER_DELETE . $processed['id'];
            }
            $ext_link = '';
            if (is_numeric($processed['current_page'])) {
                $ext_link = "&current_page=" . $processed['current_page'];
            }
            if (!empty($processed['order']) && !empty($processed['sens'])) {
                $ext_link .= "&order=" . $processed['order'] . "&sens=" . $processed['sens'];
            }
            if (str_ends_with($this->FG_LOCATION_AFTER_DELETE, "id=")) {
                header("Location: " . $this->FG_LOCATION_AFTER_DELETE . $processed['id'] . $ext_link);
            } else {
                header("Location: " . $this->FG_LOCATION_AFTER_DELETE . $ext_link);
            }
        }
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
     * Add content from SQL selects (only used in FG_var_[tariffgroup|agent|service].inc)
     */
    public function perform_add_content($form_el_index, $id)
    {
        $processed = $this->getProcessed();
        $table_split = $this->FG_EDIT_FORM_ELEMENTS[$form_el_index]["custom_query"];
        $instance_sub_table = new Table($table_split["table"]);

        $arr = is_array($processed[$table_split["name"]]) ? $processed[$table_split["name"]] : [$processed[$table_split["name"]]];
        foreach ($arr as $value) {
            $result_query = $instance_sub_table->addRow(
                $this->DBHandle,
                [$table_split["name"] => $value, $table_split["fk"] => $id]
            );

            if (!$result_query) {
                if (!str_contains($instance_sub_table->errstr, "duplicate")) {
                    echo $instance_sub_table->errstr;
                } else {
                    $this->alarm_db_error_duplication = true;
                }
            }
        }
    }


    /**
     * Delete content from SQL selects (only used in FG_var_[tariffgroup|agent|service].inc)
     */
    public function perform_del_content($form_el_index, $id)
    {
        $processed = $this->getProcessed();
        $table_split = $this->FG_EDIT_FORM_ELEMENTS[$form_el_index]["custom_query"];
        $value = trim($processed[$table_split["name"] . "_hidden"] ?? $processed[$table_split["name"]]);
        (new Table($table_split["table"]))->deleteRow(
            $this->DBHandle,
            [$table_split["name"] => $value, $table_split["fk"] => $id]
        );
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
        $list = [];

        foreach ($this->search_form_elements as &$el) {
            if ($el["type"] !== "SQL_SELECT") {
                continue;
            }
            $instance_table = new Table($el["table"], $el["columns"]);
            $list = $instance_table->get_list($this->DBHandle, $el["where"], $el["order"], $el["dir"]);
            $el["options"] = $list;
            $el["type"] = "SELECT";
        }

        echo new SearchForm($this, $processed, $list, $full_modal, $with_hide_button);
    }

    public function create_search_button(string $content = null): string
    {
        $class = "btn-outline-primary";
        $title = sprintf(_("Search %s"), $this->FG_INSTANCE_NAME);
        if (!empty($_SESSION[$this->search_session_key])) {
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

        // todo: is this ever not 0?
        $form_el_index = $processed['form_el_index'] ?? 0;

        switch ($form_action) {
            case "add-content":
                $this->perform_add_content($form_el_index, $processed['id']);
                echo new EditForm($this, $processed, $list);
                break;

            case "del-content":
                $this->perform_del_content($form_el_index, $processed['id']);
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
                if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE) > 0) {
                    call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE]);
                    // @todo should this be using the return from the function?
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
        $group ??= $this->FG_QUERY_GROUPBY_COLUMNS;
        $order ??= $this->FG_QUERY_ORDERBY_COLUMNS;
        $direction ??= $this->FG_QUERY_DIRECTION ?? "ASC";

        $_SESSION[$this->export_session_key] = [$columns, $table, $conditions, $group, $order, $direction];
    }
}
