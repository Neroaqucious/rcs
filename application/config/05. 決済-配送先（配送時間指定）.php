{# Reference: https://launchcart.jp/reference/5-%e6%b1%ba%e6%b8%88-%e9%85%8d%e9%80%81%e5%85%88%ef%bc%88%e9%85%8d%e9%80%81%e6%99%82%e9%96%93%e6%8c%87%e5%ae%9a%ef%bc%89/ #}

{# Variables Settings #}

{# insert to html head title #}
{%- set title = #delivery_datetime_label# -%}

{# end Variables Settings #}

{% TemplateInclude "header" %}

{# Contents #}

{# Breadcrumb list (this variable setting method is written in module template "breadcrumb list") #}
{%- set breadcrumbList = [] -%}
{%- set breadcrumbListItem = {
    name: #delivery_datetime_label# ,
    href: app.request.uri
  }
-%}
{%- set breadcrumbList = breadcrumbList|merge([breadcrumbListItem]) -%}
{% TemplateInclude "breadcrumb list" %}

<section class="lc-contentSection">
  <h1 class="lc-contentSection__title lc-contentSection__title--center">{{ #delivery_datetime_label# }}</h1>
  
  {# step list #}
  {%- set step = 3 -%}
  {% TemplateInclude "step list" %}

  <div class="lc-contentsInner">
    <form name="form1" id="form1" method="post" action="">
      <input type="hidden" name="convenience_store_id" value="">
        <input type="hidden" name="convenience_store_name" value="">
        <input type="hidden" name="convenience_service_type" value="">
        <input type="hidden" name="convenience_shop_type" value="">
      <section class="lc-shipList">
        <h2 class="lc-shipList__title">{{ #address_list_label# }}</h2>
        {%- if (errors|length > 0) -%}
          {%- for error in errors -%}
            <p class="lc-contentSection__note error">{{ error }}</p>
          {%- endfor -%}
        {%- endif -%}
        <ul class="lc-shipList__list">
          {%- set ship = app.session.get('ship') -%}
          {%- for consumerAddressId, tmpship in ship -%}
            {%- if (consumerAddressId > 0) -%}
              {%- set address = get_entity_manager().find('EcCoreBundle:ConsumerAddress', consumerAddressId) -%}
            {%- else -%}
              {%- set address = app.session.get('consumer_shipment_data') -%}
            {%- endif -%}
            {%- for deliveryId,tmpship2 in tmpship -%}
              {%- set delivery = get_entity_manager().find('EcCoreBundle:Delivery', deliveryId) -%}
              {%- set deliveryDay = (tmpship2['delivery_day'] is defined) ? tmpship2['delivery_day'] : '' -%}
              {%- set deliveryTime = (tmpship2['delivery_time'] is defined ) ? tmpship2['delivery_time'] : '' -%}
              {%- set pType = (tmpship2['pType'] is defined) ? tmpship2['pType'] : 'common' -%}
              {%- set isBoth = (tmpship2['isBoth'] is defined) ? tmpship2['isBoth']  : 0 -%}
              {%- set products = tmpship2[0] -%}
              <li class="lc-shipList__item">
                <div class="lc-shipList__box">
                  <div class="lc-shipList__main">
                    <header class="lc-shipList__header">
                      <h3 class="lc-shipList__name"><i class="c-inputUI c-inputUI--checkbox"></i>{{ address.lastName }} {{ address.firstName }}</h3>
                      <p class="lc-shipList__tel">{{ address.tel }}</p>
                    </header>
                    <p class="lc-shipList__address">
                      {% if address.country.id == 1 %}〒 {% endif %}{% if address.country.id != 20 %}{{ address.zipcode }}<br>{% endif %}
                      {% if address.country.id == 1 %}{{ address.district }} {% endif %}{{ address.locality }} {{ address.street }}
                    </p>
                    <ul class="lc-list">
                      {%- for product_array in products -%}
                        {%- set productsku = get_entity_manager().find("EcCoreBundle:ProductSku", product_array[0]) -%}
                        {%- set product = productsku.product -%}
                        {%- set productName = product.attribute('product name') ? product.attribute('product name') : product.name -%}
                        <li class="lc-productInfo">
                          {# product image #}
                          <figure class="lc-productInfo__figure">
                            <a class="lc-productInfo__figure__inner" href="{{ path('ec_product_detail', {'id': product.id}) }}">
                              <img src="{% if product.attribute('image1') %}{{ ('/uploads/media/'~get_entity_manager().find('MediaCoreBundle:Media', product.attribute('image1')).path) }}{% else %}{{ asset('assets/img/no-image.png') }}{% endif %}" alt="{{ productName|striptags }}"/>
                            </a>
                          </figure>
                          <div class="lc-productInfo__text">
                            {# product name and skus titles #}
                            <h2 class="lc-productInfo__name">
                              <a href="{{ path('ec_product_detail', {'id': product.id}) }}">
                                {{ productName }}
                                {% if productsku.skuDetail1 %}&emsp;{% if productsku.skuDetail1.getTransAttribute(lang_id) %}{{ productsku.skuDetail1.getTransAttribute(lang_id) }}{% else %}{{ productsku.skuDetail1.title }}{% endif %}{% endif %}
                                {% if productsku.skuDetail2 %}&emsp;{% if productsku.skuDetail2.getTransAttribute(lang_id) %}{{ productsku.skuDetail2.getTransAttribute(lang_id) }}{% else %}{{ productsku.skuDetail2.title }}{% endif %}{% endif %}
                              </a>
                            </h2>
                            {# quantity and regular interval #}
                            <div class="lc-productInfo__detail">
                              <dl class="lc-productInfo__detail__list">
                                <div class="lc-productInfo__detail__item">
                                  <dt class="lc-productInfo__detail__label">{{ #quantity_label# }}{% if  (cart_api.getRegularInt(productsku) > 0) %}/{{ #regular_interval_label# }}{% endif %}</dt>
                                  <dd class="lc-productInfo__detail__detail">
                                    {{ product_array[1]|number_format }}
                                    {%- if (product_array[2] > 0) -%}
                                      /
                                      {%- set regularInt = get_entity_manager().find('EcCoreBundle:RegularInterval',  product_array[2]) -%}
                                      {{ regularInt.getValue }}{{ ('regular.units.regular_interval.each_' ~ regularInt.getUnit) |trans({}, 'clients') }}
                                    {%- endif -%}
                                  </dd>
                                </div>
                              </dl>
                            </div>
                          </div>
                          {# additions (optional service) #}
                          {%- if (product.additions|length > 0) and (product.productType == 'common') -%}
                            <dl class="lc-productInfo__additions">
                              {%- for addition in product.additions -%}
                                <div class="lc-productInfo__additions__item">
                                  <dt class="lc-productInfo__additions__name">{% if addition.getTitleTransAttribute(lang_id) %}{{ addition.getTitleTransAttribute(lang_id) }}{% else %}{{ addition.title }}{% endif %}</dt>
                                  <dd class="lc-productInfo__additions__detail">
                                    <dl class="lc-productInfo__detail__list">
                                      <div class="lc-productInfo__detail__item">
                                        <dt class="lc-productInfo__detail__label">{{ #quantity_label# }}</dt>
                                        <dd class="lc-productInfo__detail__detail">
                                          <input type="text" value="{{ cart_api.getAdditionCount(productsku, addition) }}" size="4" maxlength="4" name="delivery_addition[{{ consumerAddressId ~ '_' ~ delivery.id ~ '_' ~ productsku.id ~ '_' ~ addition.id }}]">
                                        </dd>
                                      </div>
                                    </dl>
                                  </dd>
                                </div>
                              {%- endfor -%}
                            </dl>
                          {%- endif -%}
                        </li>
                      {%- endfor -%}
                    </ul>
                    {# Select Delivery Method and Date #}
                    <dl class="lc-shipList__options">
                      {# delivery method #}
                      <div class="lc-shipList__options__item lc-shipList__options__item--single">
                        <dt class="lc-shipList__options__label">{{ #delivery_method_label# }}</dt>
                        <dd class="lc-shipList__options__detail">
                          {%- if address.country.id == '1' -%}
                            <input type="hidden" name="flag[{{ address.id }}_{{ delivery.id }}]" value="d">
                            <input type="hidden" name="delivery[{{ address.id }}_{{ delivery.id }}]" value="{{ delivery.id }}">
                            {{ delivery.name }}
                          {%- else -%}
                            {%- set country = get_entity_manager().find('EcCoreBundle:Country', address.country.id) -%}
                            {%- set deliveryAbroads = delivery.abroads(country) -%}                          
                            <input type="hidden" name="flag[{{ address.id }}_{{ delivery.id }}]" value="ab">
                            </select>
                            {%- if (deliveryAbroads|length > 1) -%}
                              <span class="lc-selectBox">
                                <select class="lc-selectBox__select" name="delivery_abroad[{{ address.id }}_{{ delivery.id }}]" id="ec_client_cart_delivery">
                                  {%- for deliveryAbroad in deliveryAbroads -%}
                                    <option value="{{ deliveryAbroad.id }}">{{ deliveryAbroad.name }}</option>
                                  {%- endfor -%}
                                </select>
                              </span>
                            {%- else -%}
                              <input type="hidden" name="delivery_abroad[{{ address.id }}_{{ delivery.id }}]" id="ec_client_cart_delivery" value="{{ deliveryAbroads[0].id }}">
                              {{ deliveryAbroads[0].name }}
                            {%- endif -%}
                          {%- endif -%}
                          <input type="hidden" name="pType[{{ address.id }}_{{ delivery.id }}]" value="{{ pType }}">
                          <input type="hidden" name="both[{{ address.id }}_{{ delivery.id }}]" value="{{ isBoth }}">
                        </dd>
                      </div>
                      {# delivery date #}
                      <div class="lc-shipList__options__item" style="display:none;">
                        <dt class="lc-shipList__options__label">{{ #delivery_date_label# }}</dt>
                        <dd class="lc-shipList__options__detail">
                          <input type="hidden" name="delivery_day_def[{{ address.id }}_{{ delivery.id }}]" value='{{ deliveryDay }}'>
                          <span class="lc-selectBox">
                            <select class="lc-selectBox__select" name="delivery_day[{{ address.id }}_{{ delivery.id }}]" id="ec_client_cart_delivery_day">
                              <option value="">{{ #default_delivery_date# }}</option>
                            </select>
                          </span>
                        </dd>
                      </div>
                      {# delivery time #}
                      <div class="lc-shipList__options__item"  style="display:none;">
                        <dt class="lc-shipList__options__label">{{ #delivery_time_label# }}</dt>
                        <dd class="lc-shipList__options__detail">
                          <input type="hidden" name="delivery_time_def[{{ address.id }}_{{ delivery.id }}]" value="{{ deliveryTime }}">
                          <span class="lc-selectBox">
                            <select class="lc-selectBox__select" name="delivery_time[{{ address.id }}_{{ delivery.id }}]" id="ec_client_cart_delivery_time">
                              <option value="">{{ #default_delivery_time# }}</option>
                            </select>
                          </span>
                        </dd>
                      </div>
                  <p style="display:none;">※送貨日及送貨時段將備註提供給宅配業者，但實際配送狀況，仍須視宅配業者作業而定，仍有無法如期送達的狀況會發生。<br>
                  <p style="margin-top:10px;color:red">※收到發貨通知後，請多加留意手機是否有來電，宅配過程若有狀況，宅配人員可能會嘗試與收件人電話聯繫。</p>
                    </dl>
                  </div>
                </div>
              </li>
            {%- endfor -%}
          {%- endfor -%}
        </ul>
      </section>
      <p class="lc-contentsInner__buttons">
        {# to next step #}
        <button type="submit" class="lc-button--submit"><span class="lc-button__label">{{ #proceed_label# }}</span><i class="lc-button__icon lc-icon--arrow2Right"></i></button>
        {# back #}
        <a href="{{ path(app.session.get('return_route')) }}" class="lc-button--cancel"><i class="lc-button__icon lc-icon--arrowLeft"></i><span class="lc-button__label">{{ #back_label# }}</span></button>
      </p>
    </form>
  </div>
</section>

<script>
$(function() {

// on change delivery abroad
if ($('select[name^="delivery_abroad["]').length) {
  $('select[name^="delivery_abroad["]').each(function() {
    $(this)
      .on('change', function() {
        setDeliveryDateTimeOptionsByMethod($(this));
      })
      .trigger('change');
  });
}

// on change delivery (only Japan)
if ($('input[name^="delivery["]').length) {
  $('input[name^="delivery["]').each(function() {
    setDeliveryDateTimeOptionsByMethod($(this));
  });
}

// on change delivery date
if ($('select[name^="delivery_day["]').length) {
  $('select[name^="delivery_day["]').each(function() {
    $(this).on('change', function() {
      $('input[name="delivery_day_def[' + getIdByAttrName($(this)) + ']"]').val($(this).val());
    });
  });
}

// on change delivery time
if ($('select[name^="delivery_time["]').length) {
  $('select[name^="delivery_time["]').each(function() {
    $(this).on('change', function() {
      $('input[name="delivery_time_def[' + getIdByAttrName($(this)) + ']"]').val($(this).val());
    });
  });
}
});

/**
* get ID from name attribute
*/
function getIdByAttrName(elem){
var name = elem.attr('name');
return name.slice(name.indexOf('[') + 1, name.indexOf(']'));
}

/**
* set options of delivery date and time by delivery method element
* @param {jQuery} $deliveryMethod
*/
function setDeliveryDateTimeOptionsByMethod($deliveryMethod) {
var id = getIdByAttrName($deliveryMethod);
var flag = $('input[name="flag[' + id + ']"]').val();
var deliveryId = $deliveryMethod.val();
var pType = $('input[name="pType[' + id + ']"]').val();
var both = $('input[name="both[' + id + ']"]').val();
var $delivery_day = $('select[name="delivery_day[' + id + ']"]');
var $delivery_time = $('select[name="delivery_time[' + id + ']"]');
var $delivery_day_def = $('input[name="delivery_day_def[' + id + ']"]');
var $delivery_time_def = $('input[name="delivery_time_def[' + id + ']"]');

setDeliveryDayTime(flag, deliveryId, pType, both, 'day', 7, $delivery_day, '{{ #default_delivery_date# }}', $delivery_day_def.val(), 0);
setDeliveryDayTime(flag, deliveryId, pType, both,'time', 2, $delivery_time, '{{ #default_delivery_time# }}', $delivery_time_def.val(), 0);
}

/**
* set options of delivery date and time
* @param {String} flag       - if delivery to Japan then 'd', else if delivery to abroad then 'ab'.
* @param {Number} id         - delivery id
* @param {String} pType      - product type: 'common' or 'regular'
* @param {String} both       - both of product type 'common' and 'regular'
* @param {String} type       - type of get data: 'day'(delivery date), 'time'(delivery time)
* @param {Number} maxDay     - max days of set delivery date
* @param {jQuery} $select    - target select box
* @param {String} nullString - text of null option
* @param {String} def        - value of default option
* @param {Number} closeday   - flag of closeday: 0 or 1
*/
function setDeliveryDayTime(flag, deliveryId, pType, both, type, maxDay, $select, nullString, def, closeday) {
def = typeof def !== 'undefined' ?  def : 0;
closeday = typeof closeday !== 'undefined' ?  closeday : 0;
$.ajax({
  type: "GET",
  async: true,
  url: "{{ path("application_frontend_change_delivery_day") }}",
  dataType: "json",
  data: {
    "flag": flag,
    "id" : deliveryId,
    "type" :type,
    "max" : maxDay,
    "pType": pType,
    "closeday": closeday,
    "both": both
  },
}).done(function(result) {

  if (result.length > 0) {
    $select.empty();
    $select.append($('<option>', {
      value: '',
      text : nullString
    }));
    $.each(result, function (index, data) {
      isSelected = (data.key == def);
      $option = $('<option>')
        .val(data.key)
        .text(data.value)
        .prop('selected', isSelected);
      $select.append($option);
    });

  } else {
    $select.empty();
    $select.append($('<option>', {
      value: '',
      text : nullString
    }));
  }

}).fail(function ( jqXHR, textStatus, errorThrown) {
  // fail
  console.error(textStatus + ': ' + errorThrown);
})
}
</script>

{# end Contents #}
<script>
var emapData = localStorage.getItem('emapData');
if (emapData && typeof emapData === 'string') {
  emapData = JSON.parse(emapData);
}
var isDeliveryConvinience = sessionStorage.getItem('isDeliveryConvinience') == 'true' ? true : false;

$(function() {
  if ($('select[name^="delivery_abroad["]').length){
    $('select[name^="delivery_abroad["]').each(function() {
      $(this).on('change', function() {
        sendVariables($(this));
      });
      $(this).trigger( 'change' );
    });
  }

  if ($('input[name^="delivery["]').length){
    $('input[name^="delivery["]').each(function() {
      sendVariables($(this));
    });
  }

  if ($('select[name^="delivery_day["]').length){
    $('select[name^="delivery_day["]').each(function() {
      $(this).on('change', function() {
        $('input[name="delivery_day_def['+getId($(this))+']"]').val($(this).val());
      });
    });
  }

  if ($('select[name^="delivery_time["]').length){
    $('select[name^="delivery_time["]').each(function() {
      $(this).on('change', function() {
        $('input[name="delivery_time_def['+getId($(this))+']"]').val($(this).val());
      });
    });
  }
  
  {# if (emapData && isDeliveryConvinience) {
    $("input[name='convenience_store_id']").val(emapData['store_id']);
    $("input[name='convenience_store_name']").val(emapData['store_name']);
    $("input[name='convenience_service_type']").val(emapData['service_type']);
    $("input[name='convenience_shop_type']").val(emapData["shop_type"]);
    $(".lc-shipList__address").text('便利店: ' + emapData['store_id'] + ' / ' + emapData['store_name']);
    if (emapData['shop_type'] == '1') {
      $('#ec_client_cart_delivery').val( $('#ec_client_cart_delivery').children('option[value="4"]').first().val()).trigger('change');
      $('#ec_client_cart_delivery').children('option[value="1"], option[value="3"]').prop('disabled', true);
    } else {
      $('#ec_client_cart_delivery').val( $('#ec_client_cart_delivery').children('option[value="1"], option[value="3"]').first().val()).trigger('change');
      $('#ec_client_cart_delivery').children('option[value="4"]').prop('disabled', true);
    }
  } else {
    $('#ec_client_cart_delivery').val( $('#ec_client_cart_delivery').children('option[value="1"]').first().val()).trigger('change');
    $('#ec_client_cart_delivery').children('option[value="4"]').prop('disabled', true); 
    /* $('#ec_client_cart_delivery').children('option[value="3"], option[value="4"], option[value="6"], option[value="7"]').prop('disabled', true);*/
  } #}
});

function getId(elem){
  var name = elem.attr('name');
  var index1 = name.indexOf('[');
  var index2 = name.indexOf(']');
  var id = name.slice(index1+1,index2);
  return id;
}

function sendVariables(method){
  var id = getId(method);
  var flag = $('input[name="flag['+id+']"]').val();
  var deliveryId = method.val();
  var pType = $('input[name="pType['+id+']"]').val();
  var both = $('input[name="both['+id+']"]').val();
  var select_delivery_day = $('select[name="delivery_day['+id+']"]');
  var select_delivery_time = $('select[name="delivery_time['+id+']"]');
  var def_delivery_day = $('input[name="delivery_day_def['+id+']"]');
  var def_delivery_time = $('input[name="delivery_time_def['+id+']"]');

  getDeliveryDayTime(flag,deliveryId,pType,both,'day', 7, select_delivery_day, "{{ client_text.def_day }}", def_delivery_day.val(), 1);
  getDeliveryDayTime(flag,deliveryId,pType,both,'time', 2, select_delivery_time, "{{ client_text.def_time }}", def_delivery_time.val(), 1); 
}

function getDeliveryDayTime(flag,deliveryId,pType,both,type, maxDay, selectItem, nullString, def, closeday)
{
  def = typeof def !== 'undefined' ?  def : 0;
  closeday = typeof closeday !== 'undefined' ?  closeday : 0;
  $.ajax({
    type: "GET",
    async: true,
    url: "{{ path("application_frontend_change_delivery_day") }}",
    dataType: "json",
    data: {
      "flag": flag,
      "id" : deliveryId,
      "type" :type,
      "max" : maxDay,
      "pType": pType,
      "closeday": closeday,
      "both": both
    },
  }).done(function(result){

    if (result.length > 0) {
      selectItem.empty();
      selectItem.append($('<option>', {
          value: '',
          text : nullString
      }));
      $.each(result, function (index, data) {
        isSelected = (data.key == def);
        $option = $('<option>')
            .val(data.key)
            .text(data.value)
            .prop('selected', isSelected);
        selectItem.append($option);
      });

    } else {
      selectItem.empty();
      selectItem.append($('<option>', {
          value: '',
          text : nullString
      }));
    }
    if (emapData && isDeliveryConvinience) {
      $('#ec_client_cart_delivery_time > option').each(function () {
        if ($(this).val()) {
          $(this).prop('disabled', true);
        }
      });
    } else {
      $('#ec_client_cart_delivery_time > option').prop('disabled', false);
    }
  }).fail(function(result){
  });
}
</script>
{% TemplateInclude "footer" %}