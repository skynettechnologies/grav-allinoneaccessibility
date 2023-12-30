let chekedIconType = $("input:radio.iconType:checked").val();
let chekedIconSize = $("input:radio.iconSize:checked").val();
let children = document.querySelectorAll('.form-field');
let licenseKey = $(".licenseKey").val();

children[3].setAttribute('id', 'iconTypeDiv');
children[4].setAttribute('id', 'iconSizeDiv');
children[2].setAttribute('class', 'form-field grid aioa-icon-position');
children[3].setAttribute('class', 'form-field grid icon aioa-icon-type');
children[4].setAttribute('class', 'form-field grid icon aioa-icon-size');

var iDiv = document.createElement("div");
iDiv.id = 'licenseKeymsg';
document.querySelector('.form-input-wrapper').appendChild(iDiv);

var bannerDiv = document.createElement("div");
bannerDiv.id = 'dicount_banner';
document.getElementsByClassName('form-section')[0].prepend(bannerDiv);

/* add Loader Div */
// let add_element = () => {
    const template = document.createElement('div');
    template.innerHTML = '<img id="loading-image" src="../user/plugins/allinoneaccessibility/assets/img/loader.gif" alt="Loading..." />';
    template.id = 'tl_CusLoaderBox';
    
    document.getElementsByClassName('default-box-shadow')[0].prepend(template);
//}
/* add Loader Div */

if(licenseKey != null){
    checkLicenseKey(licenseKey)
}

if(chekedIconType != null){
    ChangeIcon(chekedIconType)
}

$('.iconType').change(function() {
    var iconVal = $(this).val();
    ChangeIcon(iconVal);
});

$('.licenseKey').keyup(function() {
    var licenseKey = $(this).val();
    checkLicenseKey(licenseKey)
});

function ChangeIcon(val){
    const arrSize = document.querySelectorAll(".icon-img");
    arrSize.forEach(function(item){
        item.setAttribute("src","https://skynettechnologies.com/sites/default/files/python/"+ val +".svg");
    });
}

function checkLicenseKey(key){
    console.log(key)
    //add_element();
    var server_name = window.location.hostname;
    var request = new XMLHttpRequest();
    var url =  'https://www.skynettechnologies.com/add-ons/license-api.php?';
    var params = "token=" + key +"&server_name=" + server_name;

    request.open('POST', url, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    request.onreadystatechange = function() {
        document.getElementById("tl_CusLoaderBox").style.setProperty("display", "none", "important");
        if (request.readyState === XMLHttpRequest.DONE) {
          if (request.status === 200) {

            saveData();
            var response = JSON.parse(request.response);
            
            var elementIconType = document.querySelectorAll('.iconType');
            var elementIconTypeImg = document.querySelectorAll('.icon-type-img');
            var elementIconSize = document.querySelectorAll('.iconSize');
            var elementCouponBanner = document.getElementById('dicount_banner');

            if (response.valid == 1) {
                $("#dicount_banner").hide();
                $("#iconTypeDiv").show();
                $("#iconSizeDiv").show();
                $('#licenseKeymsg').hide();
            }else{
                setCouponBanner();
                $("#iconTypeDiv").hide();
                $("#iconSizeDiv").hide();
                $('#licenseKeymsg').show();
                var domain_name = window.location.hostname;
                if(key != ''){
                    $('#licenseKeymsg').html("<span style='color:red' id='keyInvalidmsg'>Key is Invalid! </span><br><span>Please <a href='https://www.skynettechnologies.com/add-ons/cart/?add-to-cart=116&variation_id=117&variation_id=117&quantity=1&utm_source="+domain_name+"&utm_medium=getgrav-extension&utm_campaign=purchase-plan' target='_blank'>Upgrade </a> to full version of All in One Accessibility Pro.</span>")
                }else{
                    $('#licenseKeymsg').html("<span>Please <a href='https://www.skynettechnologies.com/add-ons/cart/?add-to-cart=116&variation_id=117&variation_id=117&quantity=1&utm_source="+domain_name+"&utm_medium=getgrav-extension&utm_campaign=purchase-plan' target='_blank'>Upgrade </a> to full version of All in One Accessibility Pro.</span>")
                }
            }
          }
        }
      };
      document.getElementById("tl_CusLoaderBox").style.setProperty("display", "flex", "important");;
      request.send(params);
}

function saveData(){
    var server_name = window.location.origin;
    var color = $(".color").val();
    var position = $(".position").val();
    var icon_type = chekedIconType;
    var icon_size = chekedIconSize;
    
    var request = new XMLHttpRequest();
    var url =  'https://ada.skynettechnologies.us/api/widget-setting-update-platform';
    var params = "u=" + server_name +"&widget_position=" + position +"&widget_color_code=" + color +"&widget_icon_type=" + icon_type +"&widget_icon_size="+ icon_size;

    request.open('POST', url, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    request.onreadystatechange = function() {

        if (request.readyState === XMLHttpRequest.DONE) {
          if (request.status === 200) {
            }
        }
      };
      request.send(params);
}

function setCouponBanner(){
    var coupon_url = 'https://www.skynettechnologies.com/add-ons/discount_offer.php?platform=getgrav';
    fetch(coupon_url)
    .then(function (response) {
        return response.text();
    })
    .then(function (body) {
        $("#dicount_banner").html(body);
        var domain_name = window.location.origin
    });
}