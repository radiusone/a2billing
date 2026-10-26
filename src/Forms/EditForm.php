<?php
namespace A2billing\Forms;

use A2billing\Forms\Elements\Edit\HasMany;
use A2billing\Forms\Elements\Edit\Section;
use A2billing\Forms\Elements\EditElement;

class EditForm
{
    /** @var EditElement[] */
    private array $elements = [];

    public function __construct(private readonly FormHandler $form)
    {}

    public function addElement(EditElement $edit): self
    {
        if ($edit->name === "" || array_key_exists($edit->name, $this->elements)) {
            $this->elements[] = $edit;
        } else {
            $this->elements[$edit->name] = $edit;
        }

        return $this;
    }

    public function getElement(int|string $index): EditElement|null
    {
        return $this->elements[$index] ?? null;
    }

    /**
     * @param class-string<EditElement>[] $only
     * @param class-string<EditElement>[] $except
     * @return EditElement[]
     */
    public function getElements(array $only = [], array $except = []): array
    {
        if (!$this->elements) {
            $el = [];
        } elseif (!$only && !$except) {
            $el = $this->elements;
        } elseif ($only) {
            $el = array_filter($this->elements, fn ($v) => in_array($v::class, $only));
        } else {
            $el = array_filter($this->elements, fn ($v) => !in_array($v::class, $except));
        }
        if (array_last($el) instanceof Section) {
            array_pop($el);
        }

        return $el;
    }

    public function render(array $processed, array $list, string $form_action): string
    {
        $form = $this->form;
        $db_data = $list[0] ?? [];
        switch($form_action) {
            case "ask-add":
                $template = __DIR__ . "/../../templates/AddForm.inc.php";
                $elements = $this->getElements(except: [HasMany::class]);
                break;
            case "ask-delete":
            case "ask-del-confirm":
                $template = __DIR__ . "/../../templates/DelForm.inc.php";
                $elements = array_map(
                    function (EditElement $v) {
                        $v->attributes["disabled"] = "disabled";
                        return $v;
                    },
                    $this->getElements(except: [HasMany::class])
                );
                break;
            default:
                $template = __DIR__ . "/../../templates/EditForm.inc.php";
                $elements = $this->getElements();
                break;
        }

        ob_start();
        require($template);

        return ob_get_clean() ?: "Template error!";
    }
}
