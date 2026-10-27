<?php

namespace A2billing\Forms\Traits;

use A2billing\Forms\Elements\Edit;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

trait EditMethods
{
    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @param string $check_emptyvalue If set to "NO", empty values are not validated; if set to "NO-NULL" empty values are added to the SQL query as NULL
     * @param string|null $default_value When adding (not editing), the value of the input
     * @param callable(string):string|null $custom_function A callback to run the value through before displaying it
     * @param (callable(string):string)|null $save_function A callback to run the value through before saving it to database
     * @param bool $splittable If true, value is "splittable" during create or edit (values like e.g. 12-14 or 15;16;17)
     * @return $this
     */
    public function AddEditElement(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        array $html_attributes = [],
        ?callable $validator = null,
        string $error_message = "",
        string $check_emptyvalue = "",
        ?string $default_value = "",
        ?callable $custom_function = null,
        ?callable $save_function = null,
        bool $splittable = false,
    ): static
    {
        $this->editForm->addElement(new Edit\Input(
            $label_text,
            $fieldname,
            $form_text_bottom,
            $html_attributes,
            $validator,
            $error_message,
            $check_emptyvalue,
            $default_value,
            $custom_function,
            $save_function,
            $splittable
        ));

        return $this;
    }

    /**
     * Add a split day/time field, used in common/form_data/FG_var_def_ratecard.inc
     */
    public function AddEditDayTime(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        string $default_value = "",
        string $error_message = ""
    ): static
    {
        $this->editForm->addElement(new Edit\DayTime(
            $label_text,
            $fieldname,
            $form_text_bottom,
            $default_value,
            $error_message,
        ));

        return $this;
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param Builder $table The table to check
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param array $first_option array containing a value and label (k/v) for the first options in the list
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param (callable(array):array{0:string, 1:array-key})|null $callback the callback is passed the DB row array,
     * indices 0 and 1 of the return are used to create the option label and value, otherwise
     * first two indices of the database array are used
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @return $this
     */
    public function AddEditSqlSelect(
        string $label_text,
        string $fieldname,
        Builder $table,
        string $default_value = "",
        array $first_option = [],
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = "",
        ?callable $callback = null,
        ?callable $validator = null
    ): static
    {
        $options = $table->get()
            ->when(
                $callback !== null,
                fn (Collection $rows) => $rows->map(fn (array $row) => $callback($row))
            )
            ->mapWithKeys(fn ($v) => [$v[1] => $v[0]]);

        $this->editForm->addElement(new Edit\Select(
            $label_text,
            $fieldname,
            $options,
            $default_value,
            $form_text_bottom,
            $html_attributes,
            $error_message,
            $first_option,
            $validator
        ));

        return $this;
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param array $options An array of options to build the select element with
     * @param string|int $default_value When adding (not editing) the value of the selected item
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param array<string,mixed> $first_option array containing a value and label (k/v) for the first options in the list
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @return $this
     */
    public function AddEditSelect(
        string $label_text,
        string $fieldname,
        array $options,
        string|int $default_value = "",
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = "",
        array $first_option = [],
        ?callable $validator = null,
    ): static
    {
        $this->editForm->addElement(new Edit\Select(
            $label_text,
            $fieldname,
            $options,
            $default_value,
            $form_text_bottom,
            $html_attributes,
            $error_message,
            $first_option,
            $validator,
        ));

        return $this;
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param array $options An array of data (name, value) to build radio buttons
     * @param string $default_value When adding (not editing), the value of the selected item
     * @param string $form_text_bottom Text to display below the form input
     * @param array<string,mixed> $html_attributes HTML attributes for the inputs
     * @param string $error_message A message to show if validation fails
     * @return $this
     */
    public function AddEditRadio(
        string $label_text,
        string $fieldname,
        array  $options,
        string $default_value = "",
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = ""
    ): static
    {
        $this->editForm->addElement(new Edit\Radio(
            $label_text,
            $fieldname,
            $options,
            $default_value,
            $form_text_bottom,
            $html_attributes,
            $error_message,
        ));

        return $this;
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $href The address of the popup
     * @param string $form_text_bottom Text to display below the form input
     * @param array $html_attributes HTML attributes for the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param string $error_message A message to show if validation fails
     * @return $this
     */
    public function AddEditPopup(
        string $label_text,
        string $fieldname,
        string $href,
        string $form_text_bottom = "",
        array $html_attributes = [],
        ?callable $validator = null,
        string $error_message = ""
    ): static
    {
        $this->editForm->addElement(new Edit\Popup(
            $label_text,
            $fieldname,
            $href,
            $form_text_bottom,
            $html_attributes,
            $validator,
            $error_message,
        ));

        return $this;
    }

    /**
     * Create a multi-part component to insert a record into a foreign table
     *
     * @param string $label The label text
     * @param Builder $table the table to use for display of existing records
     * @param string $insert_column the text column to edit
     * @param string $foreign_key new records will be created with this column set to the object's PK
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param bool $multiline Determines whether to use <input> or <textarea>
     * @param bool $select Determines whether to use a <select> element
     * @param string|null $pivot_table If set, $table is only used for display; $pivot table is used for updates
     * @return $this
     * @see perform_add_content()
     * @see perform_del_content()
     */
    public function AddEditHasMany(
        string $label,
        Builder $table,
        string $insert_column,
        string $foreign_key,
        ?callable $validator = null,
        bool $multiline = false,
        bool $select = false,
        ?string $pivot_table = null
    ): static
    {
        $index = $table->from . "." . $insert_column;
        $this->editForm->addElement(new Edit\HasMany(
            $label,
            $index,
            $table,
            $insert_column,
            $foreign_key,
            $validator,
            $multiline,
            $select,
            $pivot_table,
        ));

        return $this;
    }

    /**
     * @param string $label_text The label text
     * @param string $fieldname The form input name
     * @param string $form_text_bottom Text to display below the form input
     * @param array $html_attributes HTML attributes for the input
     * @param string $error_message A message to show if validation fails
     * @param string $default_value When adding (not editing) the value of the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param bool $splittable If true, value is "splittable" during create or edit (values like e.g. 12-14 or 15;16;17)
     * @return $this
     */
    public function AddEditTextarea(
        string $label_text,
        string $fieldname,
        string $form_text_bottom = "",
        array $html_attributes = [],
        string $error_message = "",
        string $default_value = "",
        ?callable $validator = null,
        bool $splittable = false,
    ): static
    {
        $this->editForm->addElement(new Edit\Textarea(
            $label_text,
            $fieldname,
            $form_text_bottom,
            $html_attributes,
            $error_message,
            $default_value,
            $validator,
            $splittable,
        ));

        return $this;
    }

    public function AddEditHidden(string $fieldname, string $value): static
    {
        $this->editForm->addElement(new Edit\Hidden($fieldname, $value));

        return $this;
    }

    public function AddEditSection(string $label): static
    {
        $this->editForm->addElement(new Edit\Section($label));

        return $this;
    }
}
