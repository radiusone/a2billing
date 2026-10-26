<?php

namespace A2billing\Forms\Traits;

use A2billing\Forms\Elements\Search;
use Illuminate\Database\Query\Builder;

trait SearchMethods
{
    /**
     * @param string $label the label for the input
     * @param string $fieldname the name of the database column, also used for HTML element names
     * @return $this
     */
    public function AddSearchDateInput(string $label, string $fieldname): static
    {
        $this->listView->addSearchElement(
            new Search\DateRange($fieldname, $label)
        );
        
        return $this;
    }

    /**
     * Adds a search input to look for relative values compared to the current date
     * This can be used to search for e.g. calls from the last week, or cards
     * created more than 8 months ago
     *
     * @param string $label
     * @param string $fieldname
     * @param bool $start determines if input date is the start (current date is end) or end (no start) of the search range
     * @param bool $months range will be 1-12 months if true, more recent otherwise (e.g. 1 hour, 1 day, 1 week, etc)
     * @return $this
     */
    public function AddSearchRelativeDateInput(string $label, string $fieldname, bool $start = false, bool $months = true): static
    {
        $this->listView->addSearchElement(
            new Search\DateRelative($fieldname, $label, $start, $months)
        );
        
        return $this;
    }

    /**
     * @param string $label the label for the input
     * @param string $fieldname the name of the database column, also used for HTML element names
     * @return $this
     */
    public function AddSearchSingleDateInput(string $label, string $fieldname): static
    {
        $this->listView->addSearchElement(
            new Search\Date($fieldname, $label)
        );
        
        return $this;
    }

    /**
     * Adds a plain text search
     *
     * @param string $displayname
     * @param string $fieldname
     * @return $this
     */
    public function AddSearchTextInput(string $displayname, string $fieldname): static
    {
        $this->listView->addSearchElement(
            new Search\Text($fieldname, $displayname)
        );
        
        return $this;
    }

    /**
     * Adds a comparison input that allows comparing the field against one or two values
     *
     * @param string $label
     * @param string $fieldname
     * @return $this
     */
    public function AddSearchComparisonInput(string $label, string $fieldname): static
    {
        $this->listView->addSearchElement(
            new Search\Comparison($fieldname, $label)
        );
        
        return $this;
    }

    /**
     * Add a SELECT element to a search form based on a database lookup
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param Builder $table a database object; first 2 columns will be used for content and value
     * @return $this
     */
    public function AddSearchSqlSelectInput(string $label, string $name, Builder $table): static
    {
        // todo: numeric keys in result
        $options = $table->get()->mapWithKeys(fn ($row) => [$row[1] => $row[0]]);
        $this->listView->addSearchElement(
            new Search\Select($name, $label, $options)
        );
        
        return $this;
    }

    /**
     * Add a SELECT element to a search form
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param array<array-key,string> $options options for the element; key is used for value attribute, value used for content
     * @param string|int $default if no current values, this item will be selected
     * @param bool $process if false, this element will NOT be used in the database query
     * @return $this
     */
    public function AddSearchSelectInput(string $label, string $name, array $options = [], int|string $default = "", bool $process = true): static
    {
        $this->listView->addSearchElement(
            new Search\Select($name, $label, $options, $default, !$process)
        );
        
        return $this;
    }

    /**
     * Add a radio button to a search form
     *
     * @param string $label the label of the element
     * @param string $name the name of the element, and also the database column queried
     * @param array<array-key,string> $options options for the element; key is used for value attribute, value used for content
     * @param bool $process if false, this element will NOT be used in the database query
     * @param string $default which value will be the default, if any
     * @return $this
     */
    public function AddSearchRadioInput(string $label, string $name, array $options = [], bool $process = true, string $default = ""): static
    {
        $this->listView->addSearchElement(
            new Search\Radio($name, $label, $options, $default, !$process)
        );
        
        return $this;
    }

    public function AddSearchPopupInput(string $label, string $name, string $href, int $select = 1): static
    {
        $this->listView->addSearchElement(
            new Search\Popup($name, $label, $href, $select)
        );
        
        return $this;
    }

    public function AddSearchButton(string $label, string $name, string $value = '1', string $class = 'btn-secondary'): static
    {
        $this->listView->addSearchElement(
            new Search\Button($name, $label, $value, $class)
        );
        
        return $this;
    }

    /**
     * @param bool $modal if true the search form will be contained in a modal, hidden by default
     * @param bool $button if false (and modal is true), the page renders its own modal trigger button
     * @param bool $delete if true, the search form will include a "deleted matching items" button
     * @param string $text sets the header text for the search form
     * @return $this
     */
    public function SetSearchType(bool $modal = false, bool $button = true, bool $delete = false, string $text = ""): static
    {
        $this->listView->setSearchParameters($modal, $button, $delete, $text);

        return $this;
    }
}
