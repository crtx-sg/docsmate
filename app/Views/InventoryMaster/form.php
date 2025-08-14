<style>
.box {
    box-shadow: 0px 1px 22px -12px #607D8B;
    background-color: #fff;
    padding: 15px 30px 15px 30px;
    border-radius: 8px;
}

.box-header {
    border-bottom: 1px solid;
    font-size: 19px !important;
}
</style>
<form class="" action="/inventory-master/<?= $action ?>" method="post">
    <div class="row pt-2 justify-content-center">
        <div class="col-6">

            <!-- <div class="alert alert-success" role="alert">
            Something
            </div> -->
            <?php if (session()->get('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= session()->get('success') ?>
            </div>
            <?php endif; ?>
            <?php if (isset($validation)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $validation->listErrors() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row p-0 pr-md-4 pl-md-4 pt-1 justify-content-center mb-2">
        <div class="col-12 col-sm-4 ">
            <div class="box">
                <div class="text-center box-header"><span>Asset Details</span></div>
                <div class="mt-3 box-body">
                    <div class="row">
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="item">Item</label>
                                <input type="text" class="form-control" name="item" id="item"
                                    value="<?= isset($member['item']) ? $member['item'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="used_by">Type</label>
                                <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                    name="type" id="type">
                                    <option value="Select Type"
                                        <?= (isset($member['type']) && ($member['type'] != '')) ? '' : 'selected' ?>>
                                        Select Type
                                    </option>
                                    <?php foreach ($assetsCategory as $assetsCat): ?>
                                    <option
                                        <?= isset($member['type']) ? (($member['type'] == $assetsCat["value"]) ? 'selected': '') : '' ?>
                                        value="<?=  $assetsCat["value"] ?>"><?=  $assetsCat["value"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="make">Make</label>
                                <input type="text" class="form-control" name="make" id="make"
                                    value="<?= isset($member['make']) ? $member['make'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="model">Model</label>
                                <input type="text" class="form-control" name="model" id="model"
                                    value="<?= isset($member['model']) ? $member['model'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="serial">Serial</label>
                                <input type="text" class="form-control" name="serial" id="serial"
                                    value="<?= isset($member['serial']) ? $member['serial'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="location">Location</label>
                                <input type="text" class="form-control" name="location" id="location"
                                    value="<?= isset($member['location']) ? $member['location'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-12 p-1">
                            <div class="form-group" id='risk_description'>
                                <label class="font-weight-bold text-muted" for="description">Description</label>
                                <textarea class="form-control" style="min-height: 185px;" name="description"
                                    id="description" maxlength=1000><?=
                isset($member['description']) ? trim($member['description']) : ''
                ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header text-center"><span>Dates</span></div>
                        <div class="box-body mt-3">
                            <div class="row">
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="entry_date">Entry Date</label>
                                        <input type="date" class="form-control" name="entry_date" id="entry_date"
                                            value="<?= isset($member['entry_date']) ? $member['entry_date'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="serial">Serial</label>
                                        <input type="text" class="form-control" name="serial" id="serial"
                                            value="<?= isset($member['serial']) ? $member['serial'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="cal_date">Cal Date</label>
                                        <input type="date" class="form-control" name="cal_date" id="cal_date"
                                            value="<?= isset($member['cal_date']) ? $member['cal_date'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="cal_due">Cal Due</label>
                                        <input type="date" class="form-control" name="cal_due" id="cal_due"
                                            value="<?= isset($member['cal_due']) ? $member['cal_due'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="retired_date">Retired Date</label>
                                        <input type="date" class="form-control" name="retired_date" id="retired_date"
                                            value="<?= isset($member['retired_date']) ? $member['retired_date'] : '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <div class="box">
                        <div class="box-header text-center"><span>Invoice Details</span></div>
                        <div class="box-body mt-3">
                            <div class="row">
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="invoice">Invoice</label>
                                        <input type="text" class="form-control" name="invoice" id="invoice"
                                            value="<?= isset($member['invoice']) ? $member['invoice'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="invoice_date">Invoice
                                            Date</label>
                                        <input type="date" class="form-control" name="invoice_date" id="invoice_date"
                                            value="<?= isset($member['invoice_date']) ? $member['invoice_date'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-12 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="vendor">Vendor</label>
                                        <input type="text" class="form-control" name="vendor" id="vendor"
                                            value="<?= isset($member['vendor']) ? $member['vendor'] : '' ?>">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
           
            if(!isset($member['id'])){
                $member['used_by'] = '';
                $member['updated_by'] = '';
            }
        ?>
        <div class="col-12 col-sm-4 ">
            <div class="box">
                <div class="box-header text-center"><span>Usage Details</span></div>
                <div class="box-body mt-3">
                    <div class="row">
                        <div class="col-12 col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="used_by">Used By</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="used_by" id="used_by">
                                    <option value="" disabled <?= isset($teamMembers[$member['used_by']]) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($teamMembers as $key=>$value): ?>
                                    <option
                                        <?= isset($member['used_by']) ? (($member['used_by'] == $key) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="updated_by">Updated By</label>

                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="updated_by" id="updated_by">
                                    <option value="" disabled <?= isset($teamMembers[$member['updated_by']]) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($teamMembers as $key=>$value): ?>
                                    <option
                                        <?= isset($member['updated_by']) ? (($member['updated_by'] == $key) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 p-1">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="status">Status</label>
                                <select class="form-control  selectpicker" data-size="8" name="status" id="status">
                                    <option value="" disabled <?= isset($member['status']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($statusList as $value): ?>
                                    <option
                                        <?= isset($member['status']) ? (($member['status'] == $value) ? 'selected': '') : '' ?>
                                        value="<?= $value ?>">
                                        <?= $value ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 justify-content-center text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</form>

<script>
$(".alert-success").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert-success").slideUp(500);
});
$(".alert-danger").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert-danger").slideUp(500);
});
function setViewCalDates(id) {
    if ($(id).val() == '') {
        $(id).val('dd/mm/yyyy');
        $("#cal_due_main, #cal_date_main").css('pointer-events', 'auto');
    } else {
        $("#cal_due_main, #cal_date_main").css('pointer-events', 'none');
        $("#cal_due").val('dd/mm/yyyy');
        $("#cal_date").val('dd/mm/yyyy');
        $("#cal_due_main, #cal_date_main").css('pointer-events', 'none');
    }

}
</script>