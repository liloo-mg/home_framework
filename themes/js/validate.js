/**
 * Created by Houlder on 20/06/2017.
 */

function validatefield(field)
{
    var valide = false;
    //var formGroup = field.parent('.form-group');
    var type = field.attr("type");
    var name = field.attr("name");
    var errorDivId = "#"+name+"-error";
    var valeur = field.val();
    var dataUtil = field.attr("data-util");
    var dataCible = field.attr("data-cible");
    var errorDiv = $('<div>');

    errorDiv.attr("id",errorDivId);
    errorDiv.addClass("text text-danger field-error");
    errorDiv.attr("style","display : none");
    field.after(errorDiv);
    /*}*/



    if (valeur == "" || valeur == null) {
        errorDiv.html("<span class=\"glyphicon glyphicon-exclamation-sign\"></span>&nbsp;Veuiller renseigner ce champ!");
        field.css("border-color","#d9534f");
        //errorDiv.show();
        valide = false;
    } else {
        field.css("border-color","#ccc");
        errorDiv.hide();
        valide = true;
    }

    if(type == "email"){
        var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
        if(reg.test(valeur)){
            field.css("border-color","#ccc");
            errorDiv.hide();
            valide = true;
        }
        else{
            field.css("border-color","#d9534f");
            valide = false ;
            errorDiv.html("<span class=\"glyphicon glyphicon-exclamation-sign\"></span>&nbsp;Cet adresse e-mail n'est pas valide");
            //errorDiv.show();
        };
    }

    if (dataUtil !== undefined && dataUtil == "confirm") {
        var valcompare = $(dataCible).val();
        if (valcompare !== valeur){
            field.css("border-color","#d9534f");
            errorDiv.html("<span class=\"glyphicon glyphicon-exclamation-sign\"></span>&nbsp;La confirmation ne correspond pas!");
            //errorDiv.show();
            valide = false;

        } else {
            field.css("border-color","#ccc");
            errorDiv.hide();
            valide = true;
        }
    }


    return valide;
}

function valideForm(form)
{

    $('.field-error').remove();
    $('.form-group').removeClass('has-error');

    var errNb = 0;
    /*var fields = */;
    form.find('.field').each(function(){
        $(this).removeClass("has-error");
        if(!validatefield($(this))){

            $(this).addClass("has-error");
            errNb++;

        };
    });

    $(".has-error").first().focus();


    var validate=(errNb>0)?false:true;
    return validate;
}

/*permet de recuperer toute fdonnée necessaire du formulaire*/

function getdataForm(form){
    var dataForm = {};
    /*var dataForm = new FormData();*/
    /*var name ='';
     var value = '';
     form.find('.util').each(function(){
     name = $(this).attr("name");
     value = $(this).val();
     dataForm[name] = value;
     });*/
    /*$.each( form.serializeArray(), function(i, field) {
        dataForm[field.name] = field.value;
        /!*dataForm.append(field.name,field.value);*!/
    });*/
    return form.serializeArray();
}

function getFormData(form) {
    var dataForm = new FormData();
    $.each( form.serializeArray(), function(i, field) {
        dataForm.append(field.name,field.value);
    });
    return dataForm;
}

function makeAjax(url,formdata,cible){
    $.post(url,formdata,function(data){
        $(cible).html(data);
    });
}