test

{strip}
    <button onclick="generate()">Generate PDF</button>
<table class="table table-bordered" {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if} align="center" style="width: 50%">
    <thead>
    <tr class="listViewHeaders">
        <th class="p-2">
            Facture
        </th>
        <th class="p-2">
            Raison sociale
        </th>
        <th class="p-2">
            Ville
        </th>
        <th class="p-2">
            réference
        </th>
        <th class="p-2">
            date valeur
        </th>
        <th class="p-2">
            Montant
        </th>
    </tr>
    </thead>
    <tbody>
    {foreach item=ENCAISSEMENT from=$ENCAISSEMENTS name=listview}

        <tr class="listViewEntries">
            <td class="p-2">
                {$ENCAISSEMENT["number"]}
            </td>
            <td class="p-2">
                {$ENCAISSEMENT["accountname"]}
            </td>
            <td class="p-2">
                {$ENCAISSEMENT["ville"]}
            </td>
            <td class="p-2">
                rf
            </td>
            <td class="p-2">
                {$ENCAISSEMENT["date_valeur"]}
            </td>
            <td class="p-2" >
                {$ENCAISSEMENT["montant"]|string_format:"%.2f"}
            </td>

        </tr>
    {/foreach}
    {foreach item=SOMME from=$SOMMES name=listview}

        <tr class="listViewEntries">
           <td colspan="4"  style="border-color: white">
        </td>
        <td colspan="1" style="border-left: 1px solid #dee2e6;">
           Total Bordereau
        </td>
            <td class="p-2" >
                {$SOMME["somme"]|string_format:"%.2f"}
            </td>

        </tr>
    {/foreach}

    </tbody>
</table>
{/strip}

<script type="text/javascript"  src ="\public_html\libraries\jsPDF\dist\jspdf.min.js"></script>

<script>
  /*  var base64Img = null;
    imgToBase64('octocat.jpg', function(base64) {
        base64Img = base64;
    });*/
    margins = {
        top: 70,
        bottom: 40,
        left: 30,
        width: 550
    };
     function generate ()
    {
        var pdf = new jsPDF('p', 'pt', 'a4');
        pdf.setFontSize(18);
        pdf.fromHTML(document.getElementById('html-2-pdfwrapper'),
            margins.left, // x coord
            margins.top,
            {
                // y coord
                width: margins.width// max width of content on PDF
            },function(dispose) {
                headerFooterFormatting(pdf, pdf.internal.getNumberOfPages());
            },
            margins);
        var iframe = document.createElement('iframe');
        iframe.setAttribute('style','position:absolute;right:0; top:0; bottom:0; height:100%; width:650px; padding:20px;');
        document.body.appendChild(iframe);

        iframe.src = pdf.output('datauristring');
    };
    function headerFooterFormatting(doc, totalPages)
    {
        for(var i = totalPages; i >= 1; i--)
        {
            doc.setPage(i);
            //header
            header(doc);

            footer(doc, i, totalPages);
            doc.page++;
        }
    };

    function header(doc)
    {
        doc.setFontSize(30);
        doc.setTextColor(40);
        doc.setFontStyle('normal');

       /* if (base64Img) {
            doc.addImage(base64Img, 'JPEG', margins.left, 10, 40,40);
        }*/

        doc.text("", margins.left + 50, 40 );
        doc.setLineCap(2);
        doc.line(3, 70, margins.width + 43,70); // horizontal line
    };

    // You could either use a function similar to this or pre convert an image with for example http://dopiaza.org/tools/datauri
    // http://stackoverflow.com/questions/6150289/how-to-convert-image-into-base64-string-using-javascript
    function imgToBase64(url, callback, imgVariable) {

        if (!window.FileReader) {
            callback(null);
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.responseType = 'blob';
        xhr.onload = function() {
            var reader = new FileReader();
            reader.onloadend = function() {
                imgVariable = reader.result.replace('text/xml', 'image/jpeg');
                callback(imgVariable);
            };
            reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', url);
        xhr.send();
    };

    function footer(doc, pageNumber, totalPages){

        var str = "Page " + pageNumber + " of " + totalPages

        doc.setFontSize(10);
        doc.text(str, margins.left, doc.internal.pageSize.height - 20);

    };

</script>
<script>
    function myFunction() {
        window.print();
    }
    function test() {
        alert('test');

    }
</script>
<style>
    .js-sidebar,.tpl-BodyHeader { display:none; }
    .o-base-container .basePanel{ margin:0;}

</style>