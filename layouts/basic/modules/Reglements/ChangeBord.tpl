<style>
    .form-control {
        width: 20%!important;
    }
</style>
<div class="o-breadcrumb__container">
    <ol class="breadcrumb breadcrumbsContainer my-0 py-auto pl-2 pr-0"><li class="breadcrumb-item"><a href="http://localhost:8080/erp/"><span class="userIcon-Home" aria-hidden="true"></span><span class="sr-only">Ma page d'accueil</span></a></li><li class="breadcrumb-item"><a href="#">Remise des bordereaux</a></li><li class="breadcrumb-item"><a href="#">Changement bordereaux</a></li></small></ol></div>
<br>
<form class="form-horizontal createCustomFieldForm"  method="POST" >
    <input type="hidden" name="module" value="Reglements">
    <input type="hidden" name="view" value="ChangeBord">
    <input type="hidden" name="mode" value="updateBordereauEdicomBanque"/>
    <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Bordereau edicom</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="bordereauEdicom" id="bordereauEdicom" REQUIRED>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label u-text-small-bold">Bordereau banque</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="bordereauBanque" value="{$BORDERAU}" id="bordereauBanque" REQUIRED>
        </div>
    </div>

    {include file=\App\Layout::getTemplatePath('EditViewActions1.tpl', $MODULE)}
</form>
