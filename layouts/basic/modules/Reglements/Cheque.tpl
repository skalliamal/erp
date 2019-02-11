<style>
    .form-control {
        width: 20%!important;
    }
</style>
{*<form class="form-horizontal createCustomFieldForm"  method="POST">
    <input type="hidden" name="module" value="Reglements">
    <input type="hidden" name="view" value="Encaissement">
    <input type="hidden" name="banque" value="{$BANQUE}">
    <input type="hidden" name="type" value="{$TYPE}">
    <input type="hidden" name="mode" value="updateBordereau"/>
    <div class="modal-body">
        <div class="form-group">
            <div class="row">
                <div class="col-md-2 ">
                    <h2>
                        Encaissement:Effet
                    </h2>
                </div>
                <div class="col-md-12 controls form-inline">
                    <div class="row">
                        <div class=" form-group ">
                            <label class="u-text-small-bold">Date Echeance</label>
                            <input type="date" class=" form-control" name="date_echeance" id="date_echeance" value="<?php echo date('d/m/Y'); ?>" required>
                        </div>
                        <div class=" form-group ">
                            <label class="u-text-small-bold">Nombre de chèques</label>
                            <input type="text" class="form-control" name="nbr_effets" id="nbr_effets" disabled>
                        </div>
                    </div>
                </div>
                   <div class="col-md-12 controls form-inline">
                    <div class="row">
                        <div class=" form-group ">
                            <label class="u-text-small-bold">Nombre de bordereaux</label>
                            <input type="text" class="form-control" name="nbr_bordereaux" id="nbr_bordereaux" readonly>
                        </div>
                        <div class=" form-group ">
                            <label class="u-text-small-bold">Bordereaux</label>
                            <input type="text" class=" form-control" name="bordereau" value="{$BORDERAU}" required>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</form>*}
<div class="o-breadcrumb__container">
    <ol class="breadcrumb breadcrumbsContainer my-0 py-auto pl-2 pr-0"><li class="breadcrumb-item"><a href="http://localhost:8080/erp/"><span class="userIcon-Home" aria-hidden="true"></span><span class="sr-only">Ma page d'accueil</span></a></li><li class="breadcrumb-item"><a href="#">Encaissement</a></li><li class="breadcrumb-item"><a href="#">Chèques</a></li></small></ol></div>
<br>
<form class="form-horizontal createCustomFieldForm"  method="POST" >
    <input type="hidden" name="module" value="Reglements">
    <input type="hidden" name="view" value="Cheque">
    <input type="hidden" name="banque" value="{$BANQUE}">
    <input type="hidden" name="type" value="{$TYPE}">
    <input type="hidden" name="mode" value="updateBordereau"/>
    <div class="form-group row">
        <label for="staticEmail" class="col-sm-2 col-form-label u-text-small-bold">Date Echeance</label>
        <div class="col-sm-10">
            <input type="date" class=" form-control" name="date_echeance" id="date_echeance" value="<?php echo date('d/m/Y'); ?>" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Nombre de chèques</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="nbr_effets" id="nbr_effets" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Nombre de bordereaux</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="nbr_bordereaux" id="nbr_bordereaux" readonly>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Bordereau</label>
        <div class="col-sm-10">
            <input type="text" class=" form-control" name="bordereau" value="{$BORDERAU}" required>
        </div>
    </div>


    {include file=\App\Layout::getTemplatePath('EditViewActions.tpl', $MODULE)}
    {*
        <button type="button" class="btn btn-light  js-popover-tooltip  FInvoice_listView_action_Exporter_en_PDF" onclick="window.location.href='index.php?module=Reglements&view=PDF&mode=generateReglement'" >PDF</button>*}
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $('#date_echeance').change(function(){
            var date_echeance =$('#date_echeance').val();
            /* var date_echeance = moment(date_echeance).format("YYYY-DD-MM");*/
            let url = "index.php?module=Reglements&view=Cheque&mode=getEncaissement&banque={$BANQUE}&type={$TYPE}&date_echeance="+date_echeance;
            AppConnector.request(url).done(function (data) {
                console.log(data);
                $('#nbr_effets').val(data);
                $('#nbr_bordereaux').val(1);
            }).fail(function (error) {
                console.log(error);
            });
        });
    });
    $('#estimation').on('click keyup change',function(){
        var bordereau =$('#date_echeance').val();
        var date_echeance =$('#date_echeance').val();
        let url = "index.php?action=Effet&record=156&mode=updateBordereauEffets&bordereau="+bordereau+"&date_echeance="+date_echeance;
        AppConnector.request(url).done(function (data) {
            console.log(data.result);
        }).fail(function (error) {
            console.log(error);
        });
    });
</script>