<?php

namespace A2billing\Forms;

use A2billing\Connection;
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
    private array $_processed = [];

    public ADOConnection $DBHandle;

    /** @var bool ??? */
    private bool $VALID_SQL_REG_EXP = true;

    /** @var mixed The result of a non-select query (insert, update, delete) */
    public $QUERY_RESULT = false;

    /* CONFIG THE VIEWER : CV */
    /** @var string Message to display if there's no data found for list view */
    public string $CV_NO_FIELDS = "THERE IS NO RECORD !";
    public string $CV_TITLE_TEXT = '';
    /** @var string Parameters to add to the URL of the list view sorting/pagination buttons */
    public string $CV_FOLLOWPARAMETERS = '';
    /** @var bool Whether to enable an archive row in list view. Used only by A2B_data_archiving.php and A2B_call_archiving.php */
    public bool $CV_DO_ARCHIVE_ALL = false;
    /** @var bool Whether to display the records-per-page dropdown. Never set to false anywhere... */
    public bool $CV_DISPLAY_RECORD_LIMIT = true;
    /** @var bool Whether to display the pagination controls. Never set to false anywhere... */
    public bool $CV_DISPLAY_BROWSE_PAGE = true;
    /** @var int Tracks the current page for pagination and DB queries */
    private int $CV_CURRENT_PAGE = 0;

    /** @var int debug level 0 (none) - 3 (lots) */
    private int $FG_DEBUG = 0;

    /** @var string The name of the element you are managing */
    public string $FG_INSTANCE_NAME = "";

    /** @var string The table name for queries */
    public string $FG_QUERY_TABLE_NAME = "";
    /** The primary key column of the table */
    public string $FG_QUERY_PRIMARY_KEY = 'id';
    /** @var string Comma separated list of columns from the SQL query to display in the list */
    public string $FG_QUERY_COLUMN_LIST = ""; // TODO: change this to an array
    /** @var string A condition to add to the list query */
    public ?string $FG_QUERY_WHERE_CLAUSE = "";
    /** @var string List of columns for the list display query to be grouped by */
    public array $FG_QUERY_GROUPBY_COLUMNS = [];
    /** @var array List of columns for the list display query to be ordered by */
    public array $FG_QUERY_ORDERBY_COLUMNS = [];
    /** @var string Direction (ASC or DESC) for the list display query ordering */
    public ?string $FG_QUERY_DIRECTION = '';
    /** @var string Default sort order */
    public string $FG_TABLE_DEFAULT_ORDER = "id";
    /** @var string Default sort direction */
    public string $FG_TABLE_DEFAULT_SENS = "ASC";


    /** @var array Data used to build the list view table */
    private array $FG_LIST_TABLE_CELLS = [];

    /** @var bool Whether to place an add button in the list view's action column */
    public bool $FG_ENABLE_ADD_BUTTON = false;

    /** @var bool Whether to place a delete button in the list view's action column */
    public bool $FG_ENABLE_DELETE_BUTTON = false;
    /** @var string The link for the delete button */
    public string $FG_DELETE_BUTTON_LINK = '';
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
    /** @var string The link for the edit button */
    public string $FG_EDIT_BUTTON_LINK = '';
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
    private array $search_form_elements = [];
    /** @var string The text for the top of the search dialog */
    public string $search_form_title = "";
    /** @var string The session variable that stores search values */
    public string $search_session_key = '';
    /** @var bool Whether to enable a delete button on the search to allow user to remove all searched items */
    public bool $search_delete_enabled = true;

    public bool $search_date_enabled = false;
    public string $search_date_text = '';
    public string $search_date_column = 'creationdate';

    public bool $search_date2_enabled = false;
    public string $search_date2_text = '';
    public string $search_date2_column = '';

    /** @var bool Whether to display a 3rd time field in search (only used in A2B_data_archiving.php) */
    public bool $search_months_ago_enabled = false;
    public string $search_months_ago_text = '';
    public string $search_months_ago_column = 'creationdate';

    /** @var bool Whether to enable a CSV export button at the bottom of a list view */
    public bool $FG_EXPORT_CSV = false;
    /** @var bool Whether to enable an XML export button at the bottom of a list view */
    public bool $FG_EXPORT_XML = false;
    /** @var string A session variable used to hold a per-export query string */
    public string $FG_EXPORT_SESSION_VAR = "";
    /** @var array List of columns to use in the export (but it is never used in the class) */
    public array $FG_EXPORT_FIELD_LIST = [];

    /** @var bool Whether to enable a custom button in the list view's action column */
    public bool $FG_OTHER_BUTTON1 = false;
    /** @var bool Whether to enable a custom button in the list view's action column */
    public bool $FG_OTHER_BUTTON2 = false;
    /** @var bool Whether to enable a custom button in the list view's action column */
    public bool $FG_OTHER_BUTTON3 = false;
    /** @var bool Whether to enable a custom button in the list view's action column (only used in FG_var_card.inc) */
    public bool $FG_OTHER_BUTTON4 = false;
    /** @var bool Whether to enable a custom button in the list view's action column (only used in FG_var_card.inc) */
    public bool $FG_OTHER_BUTTON5 = false;

    /** @var string Link for the custom button */
    public string $FG_OTHER_BUTTON1_LINK = '';
    /** @var string Link for the custom button */
    public string $FG_OTHER_BUTTON2_LINK = '';
    /** @var string Link for the custom button */
    public string $FG_OTHER_BUTTON3_LINK = '';
    /** @var string Link for the custom button */
    public string $FG_OTHER_BUTTON4_LINK = '';
    /** @var string Link for the custom button */
    public string $FG_OTHER_BUTTON5_LINK = '';

    /** @var string Image link or data URI for the custom button */
    public string $FG_OTHER_BUTTON1_IMG = '';
    /** @var string Image link or data URI for the custom button */
    public string $FG_OTHER_BUTTON2_IMG = '';
    /** @var string Image link or data URI for the custom button */
    public string $FG_OTHER_BUTTON3_IMG = '';
    /** @var string Image link or data URI for the custom button */
    public string $FG_OTHER_BUTTON4_IMG = '';
    /** @var string Image link or data URI for the custom button */
    public string $FG_OTHER_BUTTON5_IMG = '';

    /** @var string Image alt text for the custom button (or button text if no image specified) */
    public string $FG_OTHER_BUTTON1_ALT = '';
    /** @var string Image alt text for the custom button (or button text if no image specified) */
    public string $FG_OTHER_BUTTON2_ALT = '';
    /** @var string Image alt text for the custom button (or button text if no image specified) */
    public string $FG_OTHER_BUTTON3_ALT = '';
    /** @var string Image alt text for the custom button (or button text if no image specified) */
    public string $FG_OTHER_BUTTON4_ALT = '';
    /** @var string Image alt text for the custom button (or button text if no image specified) */
    public string $FG_OTHER_BUTTON5_ALT = '';

    /** @var string CSS class for the custom button */
    public string $FG_OTHER_BUTTON1_HTML_CLASS = '';
    /** @var string CSS class for the custom button */
    public string $FG_OTHER_BUTTON2_HTML_CLASS = '';
    /** @var string CSS class for the custom button */
    public string $FG_OTHER_BUTTON3_HTML_CLASS = '';
    /** @var string CSS class for the custom button */
    public string $FG_OTHER_BUTTON4_HTML_CLASS = '';
    /** @var string CSS class for the custom button */
    public string $FG_OTHER_BUTTON5_HTML_CLASS = '';

    /** @var string ID attribute for the custom button */
    public string $FG_OTHER_BUTTON1_HTML_ID = '';
    /** @var string ID attribute for the custom button */
    public string $FG_OTHER_BUTTON2_HTML_ID = '';
    /** @var string ID attribute for the custom button */
    public string $FG_OTHER_BUTTON3_HTML_ID = '';
    /** @var string ID attribute for the custom button */
    public string $FG_OTHER_BUTTON4_HTML_ID = '';
    /** @var string ID attribute for the custom button */
    public string $FG_OTHER_BUTTON5_HTML_ID = '';

    /** @var string Code which is eval'd to decide whether to show the custom button */
    public string $FG_OTHER_BUTTON1_CONDITION = '';
    /** @var string Code which is eval'd to decide whether to show the custom button */
    public string $FG_OTHER_BUTTON2_CONDITION = '';
    /** @var string Code which is eval'd to decide whether to show the custom button */
    public string $FG_OTHER_BUTTON3_CONDITION = '';
    /** @var string Code which is eval'd to decide whether to show the custom button */
    public string $FG_OTHER_BUTTON4_CONDITION = '';
    /** @var string Code which is eval'd to decide whether to show the custom button */
    public string $FG_OTHER_BUTTON5_CONDITION = '';

    //	-------------------- DATA FOR THE EDITION --------------------

    /** @var array List of form elements used to create the edit form */
    private array $FG_EDIT_FORM_ELEMENTS = [];
    /** @var array List of form elements used to create the add form (is this always the same as FG_EDIT_FORM_ELEMENTS?) */
    private array $FG_ADD_FORM_ELEMENTS = [];

    /** @var array A list of field names considered "splittable" during create or edit (values like e.g. 12-14 or 15;16;17) */
    public array $FG_SPLITABLE_FIELDS = [];

    /**
     * Set the fields  for the EDIT/ADD query
     *
     * @public    -    @type string
     */
    public string $FG_QUERY_EDITION = '';

    /** @var string A condition that will be applied to the edit query; %id is replaced within the page code */
    public string $FG_EDIT_QUERY_CONDITION = " id='%id' ";

    /** @var array list of key/value pairs that will be added to the edit form as hidden inputs */
    public array $FG_EDIT_FORM_HIDDEN_INPUTS = [];
    /** @var array list of key/value pairs that will be added to the add form as hidden inputs */
    public array $FG_ADD_FORM_HIDDEN_INPUTS = [];
    /** @var array list of key/value pairs that will be added to the edit form AND the SQL query */
    public array $FG_EDIT_QUERY_HIDDEN_INPUTS = [];
    /** @var array list of key/value pairs that will be added to the add form AND the SQL query */
    public array $FG_ADD_QUERY_HIDDEN_INPUTS = [];

    /** @var array used only in FG_var_signup.inc, should figure a way to get rid of this */
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

    /** @var string Static method of FormBO class executed before creating */
    public string $FG_ADDITIONAL_FUNCTION_BEFORE_ADD = '';
    /** @var string Static method of FormBO class executed after creating */
    public string $FG_ADDITIONAL_FUNCTION_AFTER_ADD = '';
    /** @var string Static method of FormBO class executed before deleting */
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
    private int $FG_FK_RECORDS_COUNT = 0;

    //Foreign Key Exists so Warn only not to delete ,,Boolean
    public bool $FG_FK_WARNONLY = false;

    // Delete Message for FK
    public string $FG_FK_DELETE_MESSAGE = "Are you sure to delete all records connected to this instance.";

    // TODO: this is only used by config page, surely it could be done there
    // seems to just be fetching an SQL value and then exploding it on comma

    //To enable Disable Selection List
    public bool $FG_DISPLAY_SELECT = false;
    //Selection List Field Name to get from Database
    public string $FG_SELECT_FIELDNAME = "";
    // Configuration Key value Field Name
    public string $FG_CONF_VALUE_FIELDNAME = "";

    public Logger $logger;

    private bool $FG_ENABLE_LOG = ENABLE_LOG;

    /** @var string The CSRF token for the current request */
    public string $FG_CSRF_TOKEN;

    private bool $alarm_db_error_duplication = false;

    /** @var string */
    public bool $FG_LIST_ADDING_BUTTON1 = false;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_LINK1;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_ALT1;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_IMG1;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_MSG1;

    public bool $FG_LIST_ADDING_BUTTON2 = false;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_LINK2;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_ALT2;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_IMG2;
    /** @var string */
    public string $FG_LIST_ADDING_BUTTON_MSG2;

    public function __construct(string $tablename, string $instance_name, string $primary_key = "id", ADOConnection $DBHandle = null)
    {
        Console::log('Construct FormHandler');
        Console::logMemory($this, 'FormHandler Class : Line ' . __LINE__);
        Console::logSpeed('FormHandler Class : Line ' . __LINE__);
        self::$Instance = $this;
        $this->FG_QUERY_TABLE_NAME = $tablename;
        $this->FG_INSTANCE_NAME = $instance_name;
        $this->DBHandle = $DBHandle ?? Connection::GetDBHandler();
        $this->FG_QUERY_PRIMARY_KEY = $primary_key;

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
        // Initializing anti csrf token (Generate a key, concat it with salt and hash it)
        $this->FG_CSRF_TOKEN = hash('SHA256', CSRF_SALT . $this->genCsrfTokenKey());
        $_SESSION['CSRF_TOKEN'] = $this->FG_CSRF_TOKEN;
        if ($this->FG_DEBUG) {
            echo 'CSRF NEW TOKEN : ' . $this->FG_CSRF_TOKEN . '<br />';
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
        $this->FG_TEXT_ADITION_CONFIRMATION = sprintf(_("Your new %s has been inserted"), $this->FG_INSTANCE_NAME);
        $this->FG_TEXT_ADITION_ERROR = sprintf(_("Your new %s hasn't been inserted"), $this->FG_INSTANCE_NAME);

        $this->FG_TEXT_ERROR_DUPLICATION = _("You cannot choose more than one !");
        $this->search_form_title = _("Define the search criteria");
        $this->FG_ADD_PAGE_BOTTOM_TEXT = _("Click 'Confirm Data' to continue");
        $this->FG_EDIT_PAGE_BOTTOM_TEXT = _("Click 'Confirm Data' to continue");
        $this->FG_FK_DELETE_MESSAGE = _("Are you sure you want to delete all records connected to this instance?");

        /* only modified once in admin/FG_var_signup.inc */
        $this->FG_ADD_PAGE_SAVE_BUTTON_TEXT = _('Confirm Data');

        if ($this->FG_ENABLE_LOG) {
            $this->logger = new Logger();
        }
    }


    /*
    * Generate a csrf token
    */
    private function genCsrfTokenKey(): string
    {
        $token1 = microtime();
        $token2 = uniqid(null, true);
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
        Console::logMemory($this, 'FormHandler -> init : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> init : Line ' . __LINE__);

        if (!empty($processed['section'])) {
            $section = $processed['section'];
            $_SESSION["menu_section"] = intval($section);
        }
        $ext_link = "&amp;" . http_build_query(["current_page" => $processed["current_page"] ?? "", "order" => $processed["order"] ?? "", "sens" => $processed["sens"] ?? ""], "", "&amp;");
        $this->FG_EDIT_BUTTON_LINK = "?form_action=ask-edit" . $ext_link . "&amp;id=";
        $this->FG_DELETE_BUTTON_LINK = "?form_action=ask-delete" . $ext_link . "&amp;id=";
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
            $key = str_replace("^^", ".", $key);
            if (!$this->_processed[$key] or empty($this->_processed[$key])) {
                $this->_processed[$key] = sanitize_data($value);
                if ($key == 'username') {
                    //rebuild the search parameter to filter character to format card number
                    $filtered_char = [" ", "-", "_", "(", ")", "+"];
                    $this->_processed[$key] = str_replace($filtered_char, "", $this->_processed[$key]);
                }
                if ($key == 'pwd_encoded' && !empty($value)) {
                    $this->_processed[$key] = password_hash($this->_processed[$key], PASSWORD_DEFAULT);
//                    $this->_processed[$key] = hash("whirlpool", $this->_processed[$key]);
                }
            }
        }

        return $this->_processed;
    }

    // ----------------------------------------------
    // RECIPIENT METHODS
    // ----------------------------------------------

    /**
     * Adds a table cell to the list view
     *
     * @param string $displayname
     * @param string $fieldname
     * @param bool $sortable
     * @param int|string $char_limit string is trimmed to this size before applying callbacks; 0 = no limit
     * @param callable $callback
     * @param string|null $type the cell data type
     * @param string|array|null $sql_table when type=sql|sql-link, the table to search; when type=eval, the code to evaluate; when type=list|list-conf, an array of select options
     * @param string|null $sql_cols the columns to retrieve
     * @param string|null $sql_where the condition to apply to the query; placeholder %id is replaced with the value being searched
     * @param string|null $sql_display the result field to display; one-based placeholder %n is replaced with zero-based column n from the result
     * @param string|null $destination when type=sql-link, the destination; result will be appended as query string, $sql_display will be used as link text
     */

    public function AddViewElement(
        string  $displayname,
        string  $fieldname,
        bool    $sortable = true,
                $char_limit = 0,
                $callback = "",
        ?string $type = "",
                $sql_table = "",
        ?string $sql_cols = "",
        ?string $sql_where = "",
        ?string $sql_display = "",
        ?string $destination = ""
    ): void
    {
        $this->FG_LIST_TABLE_CELLS[] = [
            "header" => $displayname,
            "field" => $fieldname,
            "sortable" => $sortable,
            "maxsize" => (int)$char_limit,
            "type" => $type,
            "sql_table" => $sql_table, // when type = lie or type = lie_link
            "code" => $sql_table, // when type = eval
            "options" => $sql_table, // when type = list or type = list-conf
            "value" => $sql_table, // when type = value
            "sql_columns" => $sql_cols, // when type = lie or type = lie_link
            "sql_clause" => $sql_where, // when type = lie or type = lie_link
            "sql_display" => $sql_display, // when type = lie or type = lie_link
            "function" => $callback,
            "href" => $destination, // when type = lie_link
        ];
    }

    //----------------------------------------------------
    // Method to Add the Field which will be included in the export file
    //----------------------------------------------------
    /*
        Add Field to FG_EXPORT_COL array, Returns Void
        *fieldname is the Field Name which will be included in the export file

    */

    public function FieldExportElement(array $fieldnames)
    {
        if (count($fieldnames)) {
            $this->FG_EXPORT_FIELD_LIST = $fieldnames;
        }
    }


    /**
     * Sets Query fieldnames for the View module
     *
     * @public
     * @ $col_query    , option to append id ( by default )
     */

    public function FieldViewElement($fieldname, $add_id = 1)
    {
        $this->FG_QUERY_COLUMN_LIST = $fieldname;
        // We need to have the ID as the last column
        if ($add_id) {
            $this->FG_QUERY_COLUMN_LIST .= ", $this->FG_QUERY_PRIMARY_KEY AS instance_primary_key";
        }
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
     * @param string $custom_function A callback to run the value through before displaying it
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
        string $custom_function = "",
        bool   $field_enabled = true // only used in FG_var_signup.inc for captcha
    )
    {
        if (!$field_enabled) {
            return;
        }
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
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
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $fieldname The form input name
     * @param string $label_text The label text
     * @param string $sql_table The table to check
     * @param string $sql_column The columns to retrieve
     * @param string $sql_where A condition to apply using a WHERE clause
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param string $first_option HTML to add before the option list
     * @param string $form_text_bottom Text to display below the form input
     * @param string $display_format A format string like "%1" which will be replaced with the first result column
     * @param string $html_attributes HTML attributes for the input
     * @param array $custom_query If provided, an array containing values to build a custom query
     * @param string $error_message A message to show if validation fails
     * @return void
     */
    public function AddEditSqlSelect(
        string $fieldname,
        string $label_text,
        string $sql_table,
        string $sql_column,
        string $sql_where = "",
        string $default_value = "",
        string $first_option = "",
        string $form_text_bottom = "",
        string $display_format = "%1",
        string $html_attributes = "",
        array  $custom_query = [],
        string $error_message = ""
    ): void
    {
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
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
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $fieldname The form input name
     * @param array $options An array of options to build the select element with
     * @param string $label_text The label text
     * @param string $form_text_bottom Text to display below the form input
     * @param string|int $default_value When adding (not editing) the value of the selected item
     * @param string $html_attributes HTML attributes for the input
     * @param string $section_name If provided, added as a row above the input
     * @param string $error_message A message to show if validation fails
     * @return void
     */
    public function AddEditSelect(
        string $fieldname,
        array  $options,
        string $label_text,
        string $form_text_bottom = "",
               $default_value = "",
        string $html_attributes = "",
        string $section_name = "",
        string $error_message = ""
    ): void
    {
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "SELECT",
            "attributes" => $html_attributes,
            "regex" => null,
            "error" => $error_message,
            "select_type" => "LIST",
            "select_fields" => $options,
            "select_format" => "%1",
            "section_name" => $section_name,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $fieldname The form input name
     * @param array $options An array of data (name, value) to build radio buttons
     * @param string $label_text The label text
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param string $form_text_bottom Text to display below the form input
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditRadio(
        string $fieldname,
        array  $options,
        string $label_text,
        string $default_value = "",
        string $form_text_bottom = "",
        string $error_message = "",
        string $section_name = ""
    ): void
    {
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
            "label" => $label_text,
            "name" => $fieldname,
            "default" => $default_value,
            "type" => "RADIOBUTTON",
            "regex" => null,
            "error" => $error_message,
            "radio_options" => $options,
            "section_name" => $section_name,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $fieldname The form input name
     * @param string $label_text The label text
     * @param string $href The address of the popup
     * @param string $form_text_bottom Text to display below the form input
     * @param string $error_message A message to show if validation fails
     * @param string $html_attributes HTML attributes for the input
     * @param int|null $regexpr_nb A validation method number
     * @param bool $is_date Whether this is a date popup used in FG_var_def_ratecard.inc
     * @return void
     */
    public function AddEditPopup(
        string $fieldname,
        string $label_text,
        string $href,
        string $form_text_bottom = "",
        string $error_message = "",
        string $html_attributes = "",
        ?int   $regex_nb = 4,
        bool   $is_date = false
    ): void
    {
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
            "label" => $label_text,
            "name" => $fieldname,
            "popup_dest" => $href,
            "popup_params" => "width=550,height=330,top=20,left=100,scrollbars=1",
            "type" => $is_date ? "POPUPDATETIME" : "POPUPVALUE",
            "attributes" => $html_attributes,
            "regex" => $regex_nb,
            "error" => $error_message,
            "comment" => $form_text_bottom,
            "validation_err" => true,
        ];
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $label_text The label text
     * @param array $query_data Data used to build the query for populating items
     * @param bool $multiline Determines whether to use <input> or <textarea>
     * @param string $section_name If provided, added as a row above the input
     * @return void
     */
    public function AddEditHasMany(
        string $label_text,
        array  $query_data,
        bool   $multiline = false,
        string $section_name = ""
    ): void
    {
        $cur = count($this->FG_EDIT_FORM_ELEMENTS);
        $data = [
            "type" => "HAS_MANY",
            "label" => $label_text,
            "custom_query" => $query_data,
            "section" => $section_name,
            "multiline" => $multiline,
            "regex" => null,
            "validation_err" => true,
        ];
        $this->FG_EDIT_FORM_ELEMENTS[$cur] = $data;
        $this->FG_ADD_FORM_ELEMENTS[$cur] = $data;
    }

    /**
     * @param string $fieldname The form input name
     * @param string $label_text The label text
     * @param string $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param string $section_name If provided, added as a row above the input
     * @param int|null $regexpr_nb A validation method number
     * @param string $form_text_bottom Text to display below the form input
     * @return void
     */
    public function AddEditTextarea(
        string $fieldname,
        string $label_text,
        string $html_attributes = "",
        string $error_message = "",
        string $section_name = "",
        ?int   $regex_nb = null,
        string $form_text_bottom = ""
    ): void
    {
        $this->FG_EDIT_FORM_ELEMENTS[] = $this->FG_ADD_FORM_ELEMENTS[] = [
            "name" => $fieldname,
            "type" => "TEXTAREA",
            "label" => $label_text,
            "section" => $section_name,
            "attributes" => $html_attributes,
            "comment" => $form_text_bottom,
            "regex" => $regex_nb,
            "error" => $error_message,
            "validation_err" => true,
        ];
    }

    /**
     * Sets Search form fieldnames for the view module
     *
     * @public
     * @ $displayname , $fieldname, $fieldvar
     */
    public function AddSearchTextInput($displayname, $fieldname, $fieldvar = null)
    {
        if (empty($fieldvar)) {
            $fieldvar = $fieldname . "type";
        }
        $this->search_form_elements[] = [
            "label" => $displayname,
            "input" => [$fieldname],
            "operator" => [$fieldvar],
            "type" => "TEXT",
        ];
    }

    public function AddSearchComparisonInput($displayname, $fieldname1, $fielvar1, $fieldname2, $fielvar2, $sqlfield)
    {
        $this->search_form_elements[] = [
            "label" => $displayname,
            "input" => [$fieldname1, $fieldname2],
            "operator" => [$fielvar1, $fielvar2],
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

    public function AddSearchSelectInput(string $displayname, string $select_name, array $array_content = [])
    {
            $this->search_form_elements[] = [
                "label" => $displayname,
                "input" => [$select_name],
                "options" => $array_content,
                "type" => "SELECT",
            ];
    }

    public function AddSearchPopupInput(string $name, string $label, string $href, int $select = 1): void
    {
        $this->search_form_elements[] = [
            "label" => $label,
            "input" => [$name],
            "href" => $href,
            "select" => $select,
            "type" => "POPUP",
        ];
    }

    /**
     * Sets Query fieldnames for the Edit/ADD module
     *
     * @public
     * @ $col_query
     */
    public function FieldEditElement($fieldname)
    {
        if ($this->FG_DISPLAY_SELECT && $this->FG_SELECT_FIELDNAME) {
            // only used by FG_var_config.inc
            $fieldname .= ", " . $this->FG_SELECT_FIELDNAME;
        }
        $this->FG_QUERY_EDITION = $fieldname;
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
     * Adds to the SQL command a comparison between a column and a posted value
     *
     * @param string $sql the existing SQL query
     * @param string $left_column the column name
     * @param string $operator post field name of the operator: 1=eq 2=lte 3=lt 4=gt 5=gte
     * @param string $post_field post field name for the comparison
     * @return string the SQL command with new comparison appended
     */
    public function do_field_duration(string $sql, string $left_column, string $operator, string $post_field): string
    {
        $processed = $this->getProcessed();

        if (!isset($processed[$post_field]) || $processed[$post_field] === "") {
            return $sql;
        }

        $sql .= str_contains($sql, 'WHERE ') ? " AND " : " WHERE ";
        $val = $processed[$post_field];
        switch ($processed[$operator] ?? null) {
            default:
                $sql .= " $left_column = '$val'";
                break;
            case 2:
                $sql .= " $left_column <= '$val'";
                break;
            case 3:
                $sql .= " $left_column < '$val'";
                break;
            case 4:
                $sql .= " $left_column > '$val'";
                break;
            case 5:
                $sql .= " $left_column >= '$val'";
                break;
        }

        return $sql;
    }

    /**
     * Adds to the SQL command a comparison between a column and a posted value
     *
     * @param string $sql the existing SQL query
     * @param string $column the column name, also used for the post field name
     * @param string $like_type post field name of the operator: 1=equal (default) 2=starts with 3=contains 4=ends with
     * @return string the SQL command with new comparison appended
     */
    public function do_field(string $sql, string $column, string $like_type = null): string
    {
        $processed = $this->getProcessed();

        if (empty($processed[$column])) {
            return $sql;
        }

        $op = $processed[$like_type] ?? 1;
        $val = $processed[$column];
        $sql .= str_contains($sql, 'WHERE ') ? " AND " : " WHERE ";

        $LIKE = "LIKE";
        $CONVERT = " COLLATE utf8_unicode_ci";
        if (DB_TYPE === "postgres") {
            $LIKE = "ILIKE";
            $CONVERT = "";
        }

        switch ($op ?? null) {
            case 1:
                $sql .= " $column='$val'";
                break;
            case 2:
                $sql .= " $column $LIKE CONCAT('$val', '%') $CONVERT";
                break;
            default:
                $sql .= " $column $LIKE CONCAT('%', '$val', '%') $CONVERT";
                break;
            case 4:
                $sql .= " $column $LIKE CONCAT('%', '$val') $CONVERT";
                break;
        }

        return $sql;
    }

    /**
     * Function to execture the appropriate action
     *
     * @public
     */
    public function perform_action(&$form_action)
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
            $this->FG_QUERY_ORDERBY_COLUMNS = array_filter([$processed['order']]);
            $this->FG_QUERY_DIRECTION = $processed['sens'] ?? "";
            $this->CV_CURRENT_PAGE = (int)$processed['current_page'];

            $session_limit = $this->FG_QUERY_TABLE_NAME . "-displaylimit";
            if (!empty((int)$_SESSION[$session_limit])) {
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
                $sql_calc_found_rows = DB_TYPE !== "postgres" ? 'SQL_CALC_FOUND_ROWS' : "";
                $cols = array_column($this->FG_LIST_TABLE_CELLS, "field");
                $fields = implode(",", $cols);
                // instance_primary_key is used to fill in links for edit/delete buttons
                $fields = "$sql_calc_found_rows $fields, $this->FG_QUERY_PRIMARY_KEY AS instance_primary_key";

                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $fields);

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

                $list = $instance_table->get_list(
                    $this->DBHandle,
                    $this->FG_QUERY_WHERE_CLAUSE,
                    $this->FG_QUERY_ORDERBY_COLUMNS,
                    $this->FG_QUERY_DIRECTION,
                    $this->FG_LIST_VIEW_PAGE_SIZE,
                    $this->CV_CURRENT_PAGE * $this->FG_LIST_VIEW_PAGE_SIZE,
                    $this->FG_QUERY_GROUPBY_COLUMNS
                );
                if ($this->FG_DEBUG === 3) {
                    echo "<br>Clause : " . $this->FG_QUERY_WHERE_CLAUSE;
                }
                if (DB_TYPE === "postgres") {
                    $this->FG_LIST_VIEW_ROW_COUNT = $instance_table->Table_count($this->DBHandle, $this->FG_QUERY_WHERE_CLAUSE);
                } else {
                    $res_count = $instance_table->SQLExec($this->DBHandle, "SELECT FOUND_ROWS() as count");
                    $this->FG_LIST_VIEW_ROW_COUNT = $res_count[0][0];
                }

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

                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $fields);
                $list = $instance_table->get_list($this->DBHandle, $this->FG_EDIT_QUERY_CONDITION, [], "ASC", 1);

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

        if ($form_action !== "list" || !$this->search_form_enabled) {
            return;
        }

        if ($processed['cancelsearch'] ?? false) {
            $_SESSION[$this->search_session_key] = '';
        }

        // RETRIEVE THE CONTENT OF THE SEARCH SESSION AND
        if ($processed['posted_search'] != 1 && strlen($_SESSION[$this->search_session_key] ?? "") > 5) {
            $element_arr = json_decode($_SESSION[$this->search_session_key], true);
            foreach ($element_arr as $entity_name => $entity_value) {
                $this->_processed[$entity_name] = $entity_value;
                if (strlen($_SESSION[$this->search_session_key]) > 10) {
                    // TODO: what does the length of this value signify? why difference between 5 and 10?
                    // below is only place it was set, and it would never be < 10
                    $processed[$entity_name] = $entity_value;
                    $_POST[$entity_name] = $entity_value;
                    $processed['posted_search'] = 1;
                }
            }
        }

        if ($processed['posted_search'] != 1) {
            return;
        }

        $this->_processed["fromstatsday_sday"] = normalize_day_of_month($processed["fromstatsday_sday"], $processed["fromstatsmonth_sday"]);
        $this->_processed["tostatsday_sday"] = normalize_day_of_month($processed["tostatsday_sday"], $processed["tostatsmonth_sday"]);
        $this->_processed["fromstatsday_sday_bis"] = normalize_day_of_month($processed["fromstatsday_sday_bis"], $processed["fromstatsmonth_sday_bis"]);
        $this->_processed["tostatsday_sday_bis"] = normalize_day_of_month($processed["tostatsday_sday_bis"], $processed["tostatsmonth_sday_bis"]);

        $SQLcmd = '';

        $search = extract_keys(
            $processed,
            "frommonth", "fromday", "fromstatsmonth", "fromsatssday_sday", "fromstatsmonth_sday",
            "tomonth", "today", "tostatsmonth", "tosatssday_sday", "tostatsmonth_sday",
            "frommonth_bis", "fromday_bis", "fromstatsmonth_bis", "fromsatssday_sday_bis", "fromstatsmonth_sday_bis",
            "tomonth_bis", "today_bis", "tostatsmonth_bis", "tosatssday_sday_bis", "tostatsmonth_sday_bis",
            "Period", "month_earlier",
        );

        foreach ($this->search_form_elements as $el) {
            foreach ($el["input"] as $i => $input) {
                $search[$input] = $processed[$input];
                if (!empty($el["operator"][$i])) {
                    $search[$el["operator"][$i]] = $processed[$el["operator"][$i]];
                }
                if ($el["type"] === "TEXT") {
                    $SQLcmd = $this->do_field($SQLcmd, $input, $el["operator"][$i]);
                } elseif ($el["type"] === "COMPARISON") {
                    $SQLcmd = $this->do_field_duration($SQLcmd, $el["column"], $el["operator"][$i], $input);
                } elseif ($el["type"] === "SELECT" || $el["type"] === "SQL_SELECT" || $el["type"] === "POPUP") {
                    $SQLcmd = $this->do_field($SQLcmd, $input);
                }
            }
        }

        $_SESSION[$this->search_session_key] = json_encode($search);

        $date_clause = '';

        if (!empty($processed['fromday']) && !empty($processed['fromstatsday_sday']) && !empty($processed['fromstatsmonth_sday'])) {
            $dt = sprintf("%s-%02d 00:00:00", $processed["fromstatsmonth_sday"], $processed["fromstatsday_sday"]);
            $date_clause .= " AND $this->search_date_column >= '$dt'";
        }
        if (!empty($processed['today']) && !empty($processed['tostatsday_sday']) && !empty($processed['tostatsmonth_sday'])) {
            $dt = sprintf("%s-%02d 23:59:59", $processed["tostatsmonth_sday"], $processed["tostatsday_sday"]);
            $date_clause .= " AND $this->search_date_column <= '$dt'";
        }

        if (($processed["Period"] ?? "") === "month_older_rad") {
            $from_month = $processed["month_earlier"];
            $date_clause .= " AND $this->search_months_ago_column < NOW() - INTERVAL $from_month MONTH";
        }

        //BIS FIELD
        if ($processed['fromday_bis'] && isset($processed['fromstatsday_sday_bis']) && isset($processed['fromstatsmonth_sday_bis'])) {
            $dt = sprintf("%s-%02d", $processed["fromstatsmonth_sday_bis"], $processed["fromstatsday_sday_bis"]);
            $date_clause .= " AND $this->search_date2_column >= '$dt'";
        }
        if ($processed['today_bis'] && isset($processed['tostatsday_sday_bis']) && isset($processed['tostatsmonth_sday_bis'])) {
            $dt = sprintf("%s-%02d 23:59:59", $processed["tostatsmonth_sday_bis"], $processed["tostatsday_sday_bis"]);
            $date_clause .= " AND $this->search_date2_column <= '$dt'";
        }

        $this->FG_QUERY_WHERE_CLAUSE = preg_replace("/^ *WHERE +/", "", $SQLcmd);
        $date_clause = preg_replace("/^ AND /", "", $date_clause);
        if ($this->FG_QUERY_WHERE_CLAUSE && $date_clause) {
            $this->FG_QUERY_WHERE_CLAUSE .= " AND ";
        }
        $this->FG_QUERY_WHERE_CLAUSE .= $date_clause;
    }

    /****************************************
     * Function to delete all pre selected records,
     * This Function Gets the selected records and delete them from DB
     ******************************************/
    public function Delete_Selected()
    {
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $this->FG_QUERY_COLUMN_LIST);
        $instance_table->Delete_Selected($this->DBHandle, $this->FG_QUERY_WHERE_CLAUSE);
    }

    /**
     * Function to perform the add action after inserting all data in required fields
     *
     * @public
     */
    public function perform_add(&$form_action): void
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;
        $param_add_fields = "";
        $param_add_value = "";
        $arr_value_to_import = [];
        $i = 0;
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME);

        foreach ($this->FG_ADD_FORM_ELEMENTS as $i => &$row) {
            if (empty($row["custom_query"])) {
                $fields_name = $row["name"];
                $regexp = $row["regex"];

                if (str_contains($row["attributes"], "multiple") && is_array($processed[$fields_name])) {
                    $total_mult_select = 0;
                    foreach ($processed[$fields_name] as $value) {
                        $total_mult_select += $value;
                    }

                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $total_mult_select;
                    }
                    if ($i > 0) {
                        $param_add_fields .= ", ";
                    }
                    $param_add_fields .= $fields_name;
                    if ($i > 0) {
                        $param_add_value .= ", ";
                    }
                    $param_add_value .= "'" . addslashes(trim($total_mult_select)) . "'";

                } else {
                    // CHECK ACCORDING TO THE REGULAR EXPRESSION DEFINED
                    if (is_numeric($regexp) && !(str_starts_with($row["check_empty"], "NO") && $processed[$fields_name] === "")) {
                        $row["validation_err"] = $this->validate_field($regexp, $processed[$fields_name]);
                        if ($row["validation_err"] !== true) {
                            $this->VALID_SQL_REG_EXP = false;
                            if ($this->FG_DEBUG == 1) {
                                echo "<br>-> $i) Error Match";
                            }
                            $form_action = "ask-add";
                        }
                    } elseif ($regexp === "check_select" && $processed[$fields_name] == -1) {
                        // FOR SELECT FIELD WE HAVE THE check_select THAT WILL ENSURE WE DEFINE A VALUE FOR THE SELECTABLE FIELD
                        $row["validation_err"] = _("Validation error");
                        $this->VALID_SQL_REG_EXP = false;
                        $form_action = "ask-add";
                    }
                    // CHECK IF THIS IS A SPLITABLE FIELD LIKE 012-014 OR 15;16;17
                    if (in_array($this->FG_SPLITABLE_FIELDS, $fields_name) && !str_starts_with($processed[$fields_name], '_')) {
                        $splitable_value = $processed[$fields_name];
                        $arr_splitable_value = explode(",", $splitable_value);
                        foreach ($arr_splitable_value as $arr_value) {
                            $arr_value = trim($arr_value);
                            $arr_value_explode = explode("-", $arr_value, 2);
                            if (count($arr_value_explode) > 1) {
                                if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1]) && $arr_value_explode[0] < $arr_value_explode[1]) {
                                    $kk = strlen($arr_value_explode[0]) - strlen(ltrim($arr_value_explode[0], '0'));
                                    $prefix = substr($arr_value_explode[0], 0, $kk);
                                    for ($kk = $arr_value_explode[0]; $kk <= $arr_value_explode[1]; $kk++) {
                                        $arr_value_to_import[] = $prefix . $kk;
                                    }
                                } elseif (is_numeric($arr_value_explode[0])) {
                                    $arr_value_to_import[] = $arr_value_explode[0];
                                } elseif (is_numeric($arr_value_explode[1])) {
                                    $arr_value_to_import[] = $arr_value_explode[1];
                                }
                            } else {
                                $arr_value_to_import[] = $arr_value_explode[0];
                            }
                        }

                        if (!empty($processed[$fields_name]) && !str_contains($row["attributes"], "disabled")) {
                            if ($i > 0) {
                                $param_add_fields .= ", ";
                            }
                            $param_add_fields .= $fields_name;
                            if ($i > 0) {
                                $param_add_value .= ", ";
                            }
                            $param_add_value .= "'%TAGPREFIX%'";
                        }
                    } else {
                        if ($this->FG_DEBUG == 1) {
                            echo "<br>$fields_name : " . $processed[$fields_name];
                        }
                        if (!empty($processed[$fields_name]) && !str_contains($row["attributes"], "disabled") && $row["type"] !== "CAPTCHAIMAGE") {
                            if ($i > 0) {
                                $param_add_fields .= ", ";
                            }
                            $param_add_fields .= $fields_name;
                            if ($i > 0) {
                                $param_add_value .= ", ";
                            }
                            $param_add_value .= "'" . addslashes(trim($processed[$fields_name])) . "'";
                        }
                    }
                }
            }
        } // endforeach with reference
        unset ($row);

        foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value) {
            $name = $instance_table->quote_identifier($name);
            // TODO: change to fully parameterized statements
            if ($value !== "now()") {
                $value = $this->DBHandle->qStr($value);
            }
            if ($i > 0) {
                $param_add_fields .= ", ";
                $param_add_value .= ", ";
            }
            $param_add_fields .= $name;
            $param_add_value .= $value;
        }

        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> $param_add_fields";
        }
        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> $param_add_value";
        }

        $res_funct = true;

        // CALL DEFINED FUNCTION BEFORE THE ADDITION

        if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_ADD) > 0 && ($this->VALID_SQL_REG_EXP)) {
            $res_funct = call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_ADD]);
        }

        if (!$res_funct) {
            return;
        }

        $instance_table = new Table($this->FG_QUERY_TABLE_NAME, $param_add_fields);
        // CHECK IF WE HAD FOUND A SPLITABLE FIELD THEN WE MIGHT HAVE %TAGPREFIX%
        if (str_contains($param_add_value, '%TAGPREFIX%')) {
            foreach ($arr_value_to_import as $current_value) {
                $param_add_value_replaced = str_replace("%TAGPREFIX%", $current_value, $param_add_value);
                if ($this->VALID_SQL_REG_EXP) {
                    $this->QUERY_RESULT = $instance_table->Add_table($this->DBHandle, $param_add_value_replaced, null, null, $this->FG_QUERY_PRIMARY_KEY);
                }
            }
        } elseif ($this->VALID_SQL_REG_EXP) {
            $this->QUERY_RESULT = $instance_table->Add_table($this->DBHandle, $param_add_value, null, null, $this->FG_QUERY_PRIMARY_KEY);
        }
        if ($this->FG_ENABLE_LOG) {
            $this->logger->insertLog_Add($_SESSION["admin_id"], 2, "NEW " . strtoupper($this->FG_INSTANCE_NAME) . " CREATED", "User added a new record in database", $this->FG_QUERY_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $param_add_fields, $param_add_value);
        }
        // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD]);
        }
        $id = $this->QUERY_RESULT;
        if (!empty($id) && ($this->VALID_SQL_REG_EXP) && (isset($this->FG_LOCATION_AFTER_ADD))) {
            if ($this->FG_DEBUG == 1) {
                echo "<br> GOTO ; " . $this->FG_LOCATION_AFTER_ADD . $id;
            }
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
        $param_update = "";
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;
        $instance_table = new Table($this->FG_QUERY_TABLE_NAME);

        if (!empty($processed['id'])) {
            $this->FG_EDIT_QUERY_CONDITION = str_replace("%id", $processed['id'], $this->FG_EDIT_QUERY_CONDITION);
        }

        foreach ($this->FG_EDIT_FORM_ELEMENTS as $i => &$row) {
            if (empty($row["custom_query"])) {
                $fields_name = $row["name"];
                $regexp = $row["regex"];

                if (str_contains($row["attributes"], "multiple") && is_array($processed[$fields_name])) {
                    $total_mult_select = 0;
                    foreach ($processed[$fields_name] as $value) {
                        $total_mult_select += $value;
                    }
                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $total_mult_select;
                    }
                    if ($i > 0) {
                        $param_update .= ", ";
                    }
                    $param_update .= "$fields_name = '" . addslashes(trim($total_mult_select)) . "'";
                } else {
                    if (is_numeric($regexp) && isset($processed[$fields_name]) && !(str_starts_with($row["check_empty"], "NO") && $processed[$fields_name] === "")) {
                        $row["validation_err"] = $this->validate_field($regexp, $processed[$fields_name]);
                        if ($row["validation_err"] !== true) {
                            $this->VALID_SQL_REG_EXP = false;
                            if ($this->FG_DEBUG == 1) {
                                echo "<br>-> $i) Error Match";
                            }
                            $form_action = "ask-edit";
                        }
                    }

                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $processed[$fields_name];
                    }
                    if ($i > 0 && $row["type"] !== "SPAN") {
                        $param_update .= ", ";
                    }
                    if (empty($processed[$fields_name]) && str_ends_with($row["check_empty"], "NULL")) {
                        $param_update .= $fields_name . " = NULL ";
                    } elseif ($row["type"] !== "SPAN") {
                        $param_update .= $fields_name . " = '" . addslashes(trim($processed[$fields_name])) . "' ";
                    }
                }
            }
        } // end foreach with reference
        unset($row);

        foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value) {
            $name = $instance_table->quote_identifier($name);
            // TODO: change to fully parameterized statements
            $value = $this->DBHandle->qStr($value);
            $param_update .= ", $name = $value";
        }

        if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION]);
        }

        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> PARAM_UPDATE: $param_update<br>" . $this->FG_EDIT_QUERY_CONDITION;
        }

        if ($this->VALID_SQL_REG_EXP) {
            $this->QUERY_RESULT = $instance_table->Update_table($this->DBHandle, $param_update, $this->FG_EDIT_QUERY_CONDITION);
        }

        if ($this->FG_ENABLE_LOG) {
            $this->logger->insertLog_Update($_SESSION["admin_id"], 3, "A " . strtoupper($this->FG_INSTANCE_NAME) . " UPDATED", "A RECORD IS UPDATED, EDITION CALUSE USED IS " . $this->FG_EDIT_QUERY_CONDITION, $this->FG_QUERY_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $param_update);
        }

        if ($this->FG_DEBUG == 1) {
            echo $this->QUERY_RESULT;
        }
        // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION]);
        }

        if ($this->VALID_SQL_REG_EXP && !empty($this->FG_LOCATION_AFTER_EDIT)) {
            if ($this->FG_DEBUG == 1) {
                echo "<br> GOTO ; " . $this->FG_LOCATION_AFTER_EDIT . $processed['id'];
            }
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
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE) > 0) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE]);
        }
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;

        $instance_table = null;
        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);

        if (($tableCount == $clauseCount) && $clauseCount > 0 && $this->FG_FK_DELETE_ALLOWED) {
            if (!empty($processed['id'])) {
                $instance_table = new Table($this->FG_QUERY_TABLE_NAME, "*", $this->FG_FK_TABLENAMES, $this->FG_FK_EDITION_CLAUSE, $processed['id'], $this->FG_FK_WARNONLY);
            }
        } else {
            $instance_table = new Table($this->FG_QUERY_TABLE_NAME);
        }
        $instance_table->FK_DELETE = !$this->FG_FK_WARNONLY;

        if (!empty($processed['id'])) {
            $this->FG_EDIT_QUERY_CONDITION = str_replace("%id", $processed['id'], $this->FG_EDIT_QUERY_CONDITION);
        }

        $this->QUERY_RESULT = $instance_table->Delete_table($this->DBHandle, $this->FG_EDIT_QUERY_CONDITION);
        if ($this->FG_ENABLE_LOG) {
            $this->logger->insertLog($_SESSION["admin_id"], 3, "A " . strtoupper($this->FG_INSTANCE_NAME) . " DELETED", "A RECORD IS DELETED, EDITION CLAUSE USED IS " . $this->FG_EDIT_QUERY_CONDITION, $this->FG_QUERY_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
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
            $rowcount += $instance_table->Table_count($this->DBHandle, $this->FG_FK_EDITION_CLAUSE[$i], $processed['id']);
        }
        $this->FG_FK_RECORDS_COUNT = $rowcount;
        if ($this->FG_DEBUG == 1) {
            echo "<br>$this->FG_FK_RECORDS_COUNT children found";
        }

        return ($rowcount > 0);
    }

    /**
     * Function to add_content
     *
     * @public
     */
    public function perform_add_content($form_el_index, $id)
    {
        $processed = $this->getProcessed();
        $table_split = $this->FG_EDIT_FORM_ELEMENTS[$form_el_index]["custom_query"];
        $instance_sub_table = new Table($table_split["table"], $table_split["name"] . ", " . $table_split["fk"]);

        $arr = is_array($processed[$table_split["name"]]) ? $processed[$table_split["name"]] : [$processed[$table_split["name"]]];
        foreach ($arr as $value) {
            if (!isset($table_split["regex"]) || $this->validate_field($table_split["regex"], $value) === true) {
                // RESPECT REGULAR EXPRESSION
                $result_query = $instance_sub_table->Add_table($this->DBHandle, "'" . addslashes(trim($value)) . "', '" . addslashes(trim($id)) . "'");

                if (!$result_query) {
                    $findme = 'duplicate';
                    $pos_find = strpos($instance_sub_table->errstr, $findme);

                    if ($pos_find === false) {
                        echo $instance_sub_table->errstr;
                    } else {
                        $this->alarm_db_error_duplication = true;
                    }
                }
            }
        }
    }


    /**
     * Function to del_content
     *
     * @public
     */
    public function perform_del_content($form_el_index, $id)
    {
        $processed = $this->getProcessed();
        $table_split = $this->FG_EDIT_FORM_ELEMENTS[$form_el_index]["custom_query"];
        if (array_key_exists($table_split["name"] . '_hidden', $processed)) {
            $value = trim($processed[$table_split["name"] . '_hidden']);
        } else {
            $value = trim($processed[$table_split["name"]]);
        }
        $instance_sub_table = new Table($table_split["table"], $table_split["name"] . ", " . $table_split["fk"]);
        $SPLIT_FG_DELETE_CLAUSE = $table_split["name"] . "='" . $value . "' AND " . $table_split["name"] . "='" . trim($id) . "'";
        $instance_sub_table->Delete_table($this->DBHandle, $SPLIT_FG_DELETE_CLAUSE);
    }


    /**
     * Function to create the top page section
     *
     * @public
     */
    public function create_toppage($form_action)
    {
        $msg = '';
        if ($form_action === "ask-edit" || $form_action === "edit" || $form_action === "add-content" || $form_action === "del-content") {
            if ($this->alarm_db_error_duplication) {
                $msg = "<p class=\"danger\">$this->FG_TEXT_ERROR_DUPLICATION</p>";
            } else {
                $msg = $this->FG_INTRO_TEXT_EDITION;
            }
        } elseif ($form_action == "ask-add") {
            $msg = $this->FG_INTRO_TEXT_ADITION;
        }
        $html = "<div class='row pb-3 align-items-center'><div class='col'>$msg</div></div>";
        echo $html;
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

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function create_search_form(bool $full_modal = false, bool $with_hide_button = true)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();
        $list = null;

        foreach ($this->search_form_elements as &$el) {
            // can't post a dot, so temporarily replace it
            if (is_array($el["input"])) {
                $el["input"][0] = str_replace(".", "^^", $el["input"][0]);
                $el["input"][1] = str_replace(".", "^^", $el["input"][1]);
            } else {
                $el["input"] = str_replace(".", "^^", $el["input"]);
            }
            if ($el["type"] !== "SQL_SELECT") {
                continue;
            }
            if (is_string($el["order"]) && str_contains($el["order"], ",")) {
                $order = explode(",", $el["order"]);
            } elseif (is_string($el["order"])) {
                $order = [$el["order"]];
            } else {
                $order = is_array($el["order"]) ? $el["order"] : [];
            }
            $instance_table = new Table($el["table"], $el["columns"]);
            $list = $instance_table->get_list($this->DBHandle, $el["where"], $order, $el["dir"]);
            $el["options"] = $list;
            $el["type"] = "SELECT";
        }
        $id = $processed['id'];
        $ratesort = $processed['ratesort'];

        require(__DIR__ . "/../../templates/SearchHandler.inc.php");

    }

    /**
     * Function to create the form
     *
     * @public
     * @noinspection PhpUnusedParameterInspection
     */
    public function create_form($form_action, $list)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();

        $id = $processed['id'];
        $ratesort = $processed['ratesort'];
        $form_el_index = $processed['form_el_index'];

        switch ($form_action) {
            case "add-content":
                $this->perform_add_content($form_el_index, $id);
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "del-content":
                $this->perform_del_content($form_el_index, $id);
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "ask-edit":
            case "edit":
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "ask-add":
                require(__DIR__ . "/../../templates/AddForm.inc.php");
                break;

            case "ask-delete":
            case "ask-del-confirm":
                if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE) > 0) {
                    $res_funct = call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE]);
                }
                if ($form_action === "ask-delete") {
                    $this->check_child_records();
                }

                require(__DIR__ . "/../../templates/DelForm.inc.php");
                break;

            case "list":
                require(__DIR__ . "/../../templates/ViewHandler.inc.php");
                break;

            case "delete":
            case "add":
                $this->create_actionfinish($form_action);
                break;

            default:
                $this->create_custom($form_action);
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

// $window = 2
// $pages = 10
// $page = 3
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
        return "<input type='hidden' name='csrf_token' value='$this->FG_CSRF_TOKEN'/>\n";
    }

    public function set_debug(int $level = 1): void
    {
        $this->FG_DEBUG = $level;
    }

    public function no_debug(): void
    {
        $this->FG_DEBUG = 0;
    }

    public function create_date_options($target): string
    {
        $month_list = [
            "", _("January"), _("February"), _("March"), _("April"), _("May"),
            _("June"), _("July"), _("August"), _("September"), _("October"),
            _("November"), _("December")
        ];
        $this_year = date("Y");
        $this_month = date("n");
        $month_year_opts = "";
        for ($i = $this_year; $i >= $this_year - 10; $i--) {
            for ($j = ($i == $this_year ? $this_month : 12); $j > 0; $j--) {
                $val = sprintf("%d-%02d", $i, $j);
                $selected = $target == $val ? 'selected="selected"' : "";
                $display = $month_list[$j] . " " . $i;
                $month_year_opts .= "<option value=\"$val\" $selected>$display</option>";
            }
        }
        return $month_year_opts;
    }
}