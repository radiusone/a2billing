<?php

use A2billing\Forms\Elements\Search\Button;
use A2billing\Forms\Elements\Search\Select;
use A2billing\Forms\FormHandler;
use A2billing\Forms\ListView;

/**
 * @var ListView $this
 * @var FormHandler $form
 * @var array $processed
 * @var bool $full_modal
 * @var bool $with_hide_button
 * @var string $action
 */
?>
<?php if ($this->searchModal && $this->searchButton): ?>
<div class="row pb-3 justify-content-center">
    <div class="col-auto">
        <button
            class="btn <?= ($processed["posted_search"] ?? 0) || $this->getSearchSession() ? "btn-primary btn-search-active" : "btn-outline-primary" ?>"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
        ><?= sprintf(_("Search %s"), $form->FG_INSTANCE_NAME) ?></button>
    </div>
</div>
<?php endif ?>
<form method="post" name="searchForm" id="searchForm" class="container-fluid form-striped" action="?<?= $action ?>">
    <input type="hidden" name="posted_search" value="1"/>
    <input type="hidden" name="current_page" value="0"/>
    <?= $form->csrf_inputs() ?>

<?php if ($this->searchModal): ?>
    <div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-search"><?= $this->searchText ?: sprintf(_("Search %s"), $form->FG_INSTANCE_NAME) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
<?php else: ?>

    <div class="row">
        <div class="col">
            <strong><?= $this->searchText ?></strong>
        </div>
    </div>
<?php endif ?>

<?php foreach ($this->getSearchElements(except: [Select::class, Button::class]) as $k => $item): ?>
    <?= $item->render($processed) ?>
<?php endforeach ?>

    <div class="row py-1">
    <?php foreach ($this->getSearchElements(only: [Select::class]) as $item): ?>
        <?= $item->render($processed) ?>
    <?php endforeach ?>
    </div>

<?php if ($this->searchModal): ?>
                </div> <!-- .modal-body -->
                <div class="modal-footer">
<?php else: ?>
    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
        <div class="col text-end">
<?php endif ?>
            <?php if ($this->getSearchSession()): ?>
                <?php if ($this->searchDelete): ?>
            <a class="btn btn-danger confirm-with-message" href="?deleteselected=true" data-message="<?= htmlspecialchars(sprintf(_("Are you sure you want to delete %d selected records?"), $form->FG_LIST_VIEW_ROW_COUNT)) ?>"><?= _("Delete") ?></a>
                <?php endif ?>
            <a class="btn btn-secondary" href="?cancelsearch=true"><?= _("Clear Search") ?></a>
            <?php endif ?>
            <?php foreach ($this->getSearchElements(only: [Button::class]) as $button): ?>
            <?= $button->render($processed) ?>
            <?php endforeach ?>
            <button type="submit" class="btn btn-primary"><?= _("Search") ?></button>
<?php if ($this->searchModal): ?>
                </div><!-- .modal-footer -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
<?php else: ?>
        </div><!-- .col -->
    </div><!-- .row -->
<?php endif ?>
</form>
