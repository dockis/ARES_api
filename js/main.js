function showMessage(msg) {
    msg = (typeof msg !== "undefined" && msg != null)? msg : '';
    return (msg != '')? "<h3>"+msg+"</h3>" : '';
}

function verifyIco(ico) {
    // test kontrolního součtu
    a = 0;
    for (i = 0; i < 7; i++) {
        a += parseInt(ico[i]) * (8 - i);
    }

    a = a % 11;
    if (a === 0) {
        c = 1;
    } 
    else if (a === 1) {
        c = 0;
    } 
    else {
        c = 11 - a;
    }

    if( parseInt(ico[7]) === c) {
        return true;
    }
    return false;
}

$(document).ready(function() {

    var localApiUrl = 'api/ares.php';
    var ares = new AresTable();
    
    function searchByIcoAjax(){
        $.ajax({
            method: "POST",
            url: localApiUrl,
            dataType: "json",
            data: { action: "search-by-ico", request_data: $("#ico").val() }
        })
        .done(function( ajax ) {
            if(ajax.status == true) {
                ares.setDetailData(ajax.data);
                $("#content").html(showMessage(ajax.message)+ares.showDetailTable());
            } else {
                $("#content").html(showMessage(ajax.message));
            }
        });
    }    
        
    $("#search-by-ico").click(function() {
        var ico = $("#ico").val().trim();
        if(verifyIco(ico)) {
            searchByIcoAjax();
        } else {
            $("#content").html(showMessage("Nesprávné IČO, nesedí formát."));
        }
    });
    
    $("#search-by-name").click(function() {
        if($("#name").val().length < 1) {
            $("#content").html(showMessage("Název je příliš krátký."));
            return;
        }
        $.ajax({
            method: "POST",
            url: localApiUrl,
            dataType: "json",
            data: { action: "search-by-name", request_data: $("#name").val() }
        })
        .done(function( ajax ) {
            if(ajax.status == true) {
                ares.reset();
                ares.setListData(ajax.data);
                $("#content").html(ares.showListTable());
            } else {
                $("#content").html(showMessage(ajax.message));
            }
        });
    });
    
    $("#content").on("click", "a.detail-button", function(){
        var detailIco = $(this).attr("id").split("-");
        $("#ico").val(detailIco[1]);
        searchByIcoAjax();
    });

    $("#content").on("click", "a.sort-button", function(){
        $("#content").html(showMessage("sorting ..."));
        ares.setSortBy($(this).attr("id"));
        ares.sortData();
        $("#content").html(ares.showListTable());
    });
    
});
