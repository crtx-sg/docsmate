<div class="row p-3 bg-white" style="border-radius:8px;">
    <div class="col">
        <h3 class="mb-0 mt-1 text-primary" style='font-size:24px;font-weight: 400;'><?php echo $pageTitle; ?></h1>
    </div>

    <?php if (isset($customDD)) : ?>
        <div class="col-auto">
            <select id="customDD" class="form-control selectpicker" data-style="<?= $customDD['style'] ?>"  title="<?= $customDD['title'] ?>" data-size="8" data-live-search="true">
                <?= $customDD['options'] ?>
            </select>
        </div>
    <?php endif; ?>
    <?php if ($addBtn) : ?>
        <div class="col-auto">
            <a href="<?= ($addUrl) ? $addUrl : '#' ?>" id="addButton" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add
            </a>
            <?php if (isset($AddMoreBtn)) : ?>
                <a href="#" class="btn btn-primary get-risks-sync">
                    <i class="fa fa-plus"></i> <?php echo $AddMoreBtnText; ?>
                </a>
            <?php endif; ?>
        </div>

    <?php elseif (isset($titleDD)) : ?>
        <div class="col-auto">
            <select class="form-control selectpicker text-light" id="newDoc" data-style="btn-primary" data-live-search="true" data-size="8">
                <option value="" selected disabled>New Document</option>
                <?php foreach ($documentType as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php else : ?>
        <div class="col-auto ">
            <a class="btn btn-secondary text-light" href="<?= $backUrl ?>">
                <i class="fa fa-chevron-left"></i> Back
            </a>
        </div>
    <?php endif; ?>
</div>
</div>