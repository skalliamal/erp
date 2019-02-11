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
    <ol class="breadcrumb breadcrumbsContainer my-0 py-auto pl-2 pr-0"><li class="breadcrumb-item"><a href="http://localhost:8080/erp/"><span class="userIcon-Home" aria-hidden="true"></span><span class="sr-only">Ma page d'accueil</span></a></li><li class="breadcrumb-item"><a href="#">Encaissement</a></li><li class="breadcrumb-item"><a href="#">Effets</a></li></small></ol></div>
<br>
<form class="form-horizontal createCustomFieldForm"  method="POST" >
        <input type="hidden" name="module" value="Reglements">
        <input type="hidden" name="view" value="Effet">
        <input type="hidden" name="banque" value="{$BANQUE}">
        <input type="hidden" name="type" value="{$TYPE}">
        <div class="form-group row">
            <label for="staticEmail" class="col-sm-2 col-form-label u-text-small-bold">Date Echeance</label>
            <div class="col-sm-10">
                <input type="date" class=" form-control" name="date_echeance" id="date_echeance" value="<?php echo date('d/m/Y'); ?>" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Nombre d'effets</label>
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
                <input type="text" class=" form-control" name="bordereau" id="bordereau" value="{$BORDERAU}" required>
            </div>
        </div>
        {include file=\App\Layout::getTemplatePath('EditViewActions.tpl', $MODULE)}
{*
    <button type="button" class="btn btn-light  js-popover-tooltip  FInvoice_listView_action_Exporter_en_PDF" onclick="window.location.href='index.php?module=Reglements&view=PDF&mode=generateReglement'" >PDF</button>*}
        {*<button type="button" class="btn btn-outline-dark  js-popover-tooltip  Reglements_detailViewBasic_action_Exporter_en_PDF"  name="mode" value="genererPDF" data-js="popover" data-placement="bottom" data-content="Exporter en PDF" data-target="focus hover" onclick="Vtiger_Header_Js.getInstance().showPdfModal('index.php?module=Reglements&view=PDF&fromview=Detail&record=465');" data-original-title="" title=""><svg class="svg-inline--fa fa-file-excel fa-w-12" title="Exporter en PDF" aria-labelledby="svg-inline--fa-title-18" data-prefix="fas" data-icon="file-excel" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg=""><title id="svg-inline--fa-title-18">Exporter en PDF</title><path fill="currentColor" d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z"></path></svg><!-- <span class="fas fa-file-excel " title="Exporter en PDF"></span> --><span class="d-md-none ml-1">Exporter en PDF</span></button>
    *}
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $('#date_echeance').change(function(){
            var date_echeance =$('#date_echeance').val();
            /* var date_echeance = moment(date_echeance).format("YYYY-DD-MM");*/
            let url = "index.php?module=Reglements&view=Effet&mode=getEncaissement&banque={$BANQUE}&type={$TYPE}&date_echeance="+date_echeance;
            AppConnector.request(url).done(function (data) {
                console.log(data);
                $('#nbr_effets').val(data);
                $('#nbr_bordereaux').val(1);
            }).fail(function (error) {
                console.log(error);
            });
        });
         $('#update').click(function(){
            var date_echeance =$('#date_echeance').val();
            var bordereau =$('#bordereau').val();
             /*var date_echeance = moment(date_echeance).format("YYYY-DD-MM");*/
            let url = "index.php?module=Reglements&view=Effet&mode=updateBordereau&banque={$BANQUE}&type={$TYPE}&date_echeance="+date_echeance+"&bordereau="+bordereau;
            AppConnector.request(url).done(function (data) {
                console.log(data);
                let params = { text: "Bordereau modifié avec succès!", type: 'success'};
                Vtiger_Helper_Js.showPnotify(params);
            }).fail(function (error) {
                console.log(error);
                Vtiger_Helper_Js.showPnotify('Bordereau non modifié!');
            });
        });


    });

</script>