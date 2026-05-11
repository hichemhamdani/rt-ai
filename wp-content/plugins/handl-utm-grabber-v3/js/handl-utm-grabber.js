// (function(history){
//     var replaceState = history.replaceState;
//     history.replaceState = function(state) {
//         if (typeof history.onreplacestate == "function") {
//             history.onreplacestate({state: state});
//         }
//         return replaceState.apply(history, arguments);
//     };
// })(window.history);
//
// window.onpopstate = history.onreplacestate = function(e) {
//     var domain = getDomainName()
//     console.log(e.state.path)
//     Cookies.set('handl_url', e.state.path, {
//         expires: parseInt(handl_utm_cookie_duration[0]),
//         domain: domain,
//         sameSite:'None',
//         secure: true }
//     );
// }

var qvars = getUrlVars()
var domain = getDomainName()

jQuery(function($) {
    if (handl_utm_cookie_duration[1] === '1' &&
        typeof(TVE) === "undefined" //thrive editor fix
    ){
        console.log('Running HandL');
        RunHandL()

        const borlabsCookie = Cookies.get('borlabs-cookie'); // Get the cookie as a JSON string
        if (borlabsCookie) {
            try {
                // Parse the JSON string into an object
                const cookieData = JSON.parse(borlabsCookie);

                // Check if the 'marketing' key has 'handl-utm-grabber'
                if (cookieData.consents.marketing.includes('handl-utm-grabber')) {
                    console.log("handl-utm-grabber checked by Borlabs.. RunHandL...");
                    RunHandL()
                } else {
                    console.log("handl-utm-grabber not checked by Borlabs"); // or return false;
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
            }
        }
    }
});


function RunHandL(){
    window.utms_js = {}
    window.gaNames = []

    //simulate referrer locally
    // Object.defineProperty(document, "referrer", {get : function(){ return "https://google.com"; }});

    SetRefLink('handlID', Math.floor(Math.random() * Date.now()), false, 0)

    GAClientID()
    setHandLParams()

    // console.log("after handl params")

    RunFieldFiller()
    // Occasionally, the page takes longer to render, so it makes sense to introduce a delay. However, we have not yet investigated the potential negative effects of doing this when it is not necessary.
    // setTimeout(RunFieldFiller, 1000)

    jQuery.each(handl_utm_predefined, function( i,v ) {
        let cookie_name = v.name;
        let value = v.value

        if ( value.startsWith('[') && value.endsWith(']') ){ //it's a shortcode!
            value = v.value.replace(/^\[|\]$/g,'');
        }else{ //it is a hardcoded default value
            value = cookie_name
        }

        if ( ['_ga','gaclientid'].indexOf(value) > -1 ){
            gaNames.push(cookie_name)
        }

        if (cookie_name != ''){
            var cookie_field = GetQVars(value,qvars)

            if (cookie_field == ''){
                cookie_field = Cookies.get(value)
            }

            if ( cookie_field != '' && cookie_field != 'PANTHEON_STRIPPED' ){
                utms_js[cookie_name] = cookie_field
                SetRefLink(cookie_name, cookie_field, true, 0)
            }
        }
    })

    populateLinks()

    jQuery(document).on( 'nfFormReady', function() {
        // Loop through each form in nfForms array
        nfForms.forEach(form => {
            form.fields.map(item => {
                if (item.default) {
                    var matches = String(item.default).match(/^{(\w+):(\w+)/)
                    if (matches && matches.length == 3) {
                        let key = matches[1]
                        let value = matches[2]

                        if (key === 'handl') {
                            console.log(key, value)
                            jQuery('#nf-field-' + item.id).val(Cookies.get(value))
                        }
                    }
                }
            })
        })

        //hide hidden fields
        // jQuery('.nf-field-container.hidden-container').map( (i, item) => jQuery(item).parents().eq(4).hide())
    })

    document.dispatchEvent(new Event('HandL-After-Main-Function'));
}

function RunFieldFiller(){

    let cookieOptions = {
        domain: domain,
        secure: true,
        sameSite: 'None'
    };

    if (!parseInt(handl_utm_session_cookies?.enabled)) {
        cookieOptions.expires = parseInt(handl_utm_cookie_duration[0]);
    }

    Cookies.set('user_agent', navigator.userAgent, cookieOptions)

    jQuery.each(handl_utm_all_params, function( i,vi ) {
        var cookie_field = GetQVars(vi,qvars)

        var cookie_params = [vi]
        if ( handl_utm_first_touch_params.includes(vi) )
            cookie_params.push('first_'+vi)

        cookie_params.forEach( (v, ind) => {

            if ( cookie_field != '' && cookie_field != 'PANTHEON_STRIPPED' ){
                if ( v.startsWith('first_') && Cookies.get(v) != undefined ){
                            //do not update the first attributes...
                }else{
                    if (cookie_field.includes('#')) {
                        cookie_field = cookie_field.split('#')[0];
                    }

                    let cookieOptions = {
                        domain: domain,
                        sameSite: 'None',
                        secure: true
                    };

                    if (!parseInt(handl_utm_session_cookies?.enabled)) {
                        cookieOptions.expires = parseInt(handl_utm_cookie_duration[0]);
                    }

                    Cookies.set(v, cookie_field, cookieOptions);
                }
            }

            var curval = decodeURI(Cookies.get(v))
            curval = curval != 'undefined' ? curval : ''
            if (curval != 'undefined') {
                curval = sanitizeInput(curval);
                utms_js[v] = curval
                curval = decodeURIComponent(curval)
                // curval = curval.replace(/[%]/g,' ')
                if (v == 'username') {
                    //Maybe this should apply to all... We'll see...
                    curval = curval.replace(/\+/g, ' ')
                }

                jQuery('input[name=\"'+v+'\"]').val(curval)
                jQuery('input#'+v).val(curval)
                jQuery('input.'+v).val(curval)
                jQuery('input#form-field-'+v).val(curval)

                //for nested input fix
                jQuery('#'+v).find('input').val(curval)
                jQuery('.'+v).find('input').val(curval)

                jQuery("[data-original_id='"+v+"']").val(curval)
                jQuery("[data-name='"+v+"']").val(curval)
                jQuery("[data-name='"+v.replace(/_/g,'')+"']").val(curval) //Active Campaign



                if (v.length > 4){ // this is for making sure wildcards are not hyper sensitive.
                    //wildcard selector
                    jQuery("[name*="+v+"]").val(curval)
                    jQuery("[id*="+v+"]").val(curval)
                    jQuery("[class*="+v+"]").val(curval)

                    jQuery("[class*="+v+"_out]").html(curval)
                    jQuery("[id*="+v+"_out]").html(curval)
                }

            }
        })
    });
}

function populateLinks(){
    jQuery('.utm-out-js, .utm-out-js a').each(function(){
        var merged_raw = jQuery.extend( {}, utms_js )
        var merged = Object.keys(merged_raw)
            .filter(key => handl_utm_append_params.includes(key))
            .reduce((obj, key) => {
                if (merged_raw[key]) {
                    obj[key] = decodeURIComponent(merged_raw[key]);
                }
                return obj;
            }, {});
        merged = jQuery.extend(merged, getSearchParams(this.href))
        var current_page = window.location.href.split('?')[0]
        if (
            this.href !== undefined &&
            this.href !== "" &&
            !this.href.match("#") &&
            !this.href.startsWith("tel:") &&
            !this.href.startsWith("mailto:") &&
            !this.href.startsWith("sms:") &&
            !jQuery(this).attr('onclick') &&
            !jQuery(this).closest('.no-utm').length // Check if the link or any of its parents have the no-utm class
            // this.href.match(new RegExp("^"+current_page+"$")) == null
        ){
            var href = this.href.split("?")[0];
            if ( !jQuery.isEmptyObject(merged) )
                this.href = href+"?"+decodeURIComponent(jQuery.param(merged))
        }
    })  ;

    // console.log(handl_utm)
    // console.log(utms_js)

    jQuery('.utm-out, .utm-out a').each(function(){
        var merged_raw = jQuery.extend( {}, handl_utm, utms_js )
        var merged = Object.keys(merged_raw)
            .filter(key => handl_utm_append_params.includes(key))
            .reduce((obj, key) => {
                if (merged_raw[key]) {
                    obj[key] = decodeURIComponent(merged_raw[key]);
                }
                return obj
            }, {});
        merged = jQuery.extend(merged, getSearchParams(this.href))
        var current_page = window.location.href.split('?')[0]
        if (
            this.href !== undefined &&
            this.href !== "" &&
            !this.href.match("#") &&
            !this.href.startsWith("tel:") &&
            !this.href.startsWith("mailto:") &&
            !this.href.startsWith("sms:") &&
            !jQuery(this).closest('.no-utm').length // Check if the link or any of its parents have the no-utm class
            // !jQuery(this).attr('onclick') &&
            // this.href.match(new RegExp("^"+current_page+"$")) == null
        ){
            var href = this.href.split("?")[0];
            var parts = []
            if (this.href.match("#")){
                parts = this.href.split("#")
                if (parts[1] != ""){
                    href = parts[0]
                }
            }

            // console.log(parts, href)
            if ( !jQuery.isEmptyObject(merged)  ){
                if (this.href.match("#") && parts.length == 2){
                    this.href = href+"?"+decodeURIComponent(jQuery.param(merged))+"#"+parts[1].split("?")[0]
                }else{
                    this.href = href+"?"+decodeURIComponent(jQuery.param(merged))
                }
            }
        }
    });

    jQuery('.utm-src').each(function(){
        var target_url
        if (this.src){
            target_url = this.src
        }else if (jQuery(this).data('url')){
            target_url = jQuery(this).data('url')
        }else if (jQuery(this).data('src')){
            target_url = jQuery(this).data('src')
        }

        var merged_raw = jQuery.extend( {}, handl_utm, utms_js )
        var merged = Object.keys(merged_raw)
            .filter(key => handl_utm_append_params.includes(key))
            .reduce((obj, key) => {
                if (merged_raw[key]) {
                    obj[key] = decodeURIComponent(merged_raw[key]);
                }
                return obj;
            }, {});
        merged = jQuery.extend( merged, getSearchParams(target_url) )

        var src = target_url.split("?")[0];
        if ( !jQuery.isEmptyObject(merged) ){
            var final_target = src + "?" + decodeURIComponent(jQuery.param(merged))
            if (this.src) {
                this.src = final_target
            }else if (jQuery(this).data('url')){
                jQuery(this).data('url', final_target)
            }
        }
    });

    jQuery("[data-fillout-id]").each(function(){
        handl_utm_append_params.map(t=>{
            jQuery(this).attr(`data-${t}`, utms_js[t])
        })

        var script = document.createElement('script');
        script.src ='https://server.fillout.com/embed/v1/';
        document.head.appendChild(script);

    })
}

function getSearchParams(url,k){
    var p={};
    var a = document.createElement('a');
    a.href = url;
    a.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){p[k]=v})
    return k?p[k]:p;
}

function GetQVars(v,qvars){
    if (qvars[v] != undefined) {
        return qvars[v]
    }
    return ''
}

function getUrlVars() {
    var vars = {};
    window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = decodeURIComponent(value);
    });
    return vars;
}

function GAClientID(){
    if ( typeof(ga) == 'function' && typeof(ga.getAll) == 'function'){
        var trackers = ga.getAll();
        if (trackers.length > 0 ){
            //maybe we'll update this later, so we can loop all the tracking ids...
            var entries = Object.entries({"clientId":"gaclientid","referrer":"organic_source"})
            var domain = getDomainName()
            for (const [gaName, handlName] of entries) {
                var gaValue = trackers[0].get(gaName)
                if (gaValue !== undefined) {
                    if (gaName != 'clientId'){ // we should reset the array for anythng other than clientId
                        gaNames = []
                    }

                    gaNames.push(handlName)

                    for (const curName of gaNames){
                        // console.log(curName, gaValue)
                        utms_js[curName] = gaValue
                        let cookieOptions = {
                            domain: domain,
                            sameSite: 'None',
                            secure: true
                        };

                        if (!parseInt(handl_utm_session_cookies?.enabled)) {
                            cookieOptions.expires = parseInt(handl_utm_cookie_duration[0]);
                        }

                        Cookies.set(curName, gaValue, cookieOptions);
                        // console.log(`Setting ${handlName} as ${gaValue}`)
                        jQuery('input[name=\"' + curName + '\"]').val(gaValue)
                        jQuery('input#' + curName).val(gaValue)
                        jQuery('input.' + curName).val(gaValue)
                    }

                    // so we can include gaclientid to the links incase needed
                    populateLinks()
                }
            }
        }
    }else{
        setTimeout(GAClientID,500);
    }
}

function getDomainName(){
    var name="HandLtestDomainName"
    var value="HandLtestDomainValue"
    var host=location.host
    var domain;

    if (host.split('.').length === 1){
        domain = '';
    }else{
        var domainParts = host.split('.');
        domainParts.shift();
        domain = '.'+domainParts.join('.');
        Cookies.set(name, value, {
            domain: domain,
            secure: true,
            sameSite: 'None'
        })
        if (Cookies.get(name) == null || Cookies.get(name) != value)
        {
            domain = '.'+host;
        }
    }

    return domain;
}

function setHandLParams(){
    // console.log("set handl params")

    SetRefLink('handl_url_base', document.location.origin + document.location.pathname, true, 0)
    SetRefLink('handl_url', document.location.href, true, 0)
    SetRefLink('handl_ref', document.referrer, true, 0)
    SetRefLink('handl_ref_domain', document.referrer == '' ? '' : this.get_url_domain(document.referrer), true, 0)
    SetRefLink('handl_landing_page', document.location.href, false, 0)
    SetRefLink('handl_landing_page_base', new URL(document.location.href).origin + new URL(document.location.href).pathname, false, 0)
    SetRefLink('handl_original_ref', document.referrer, false, 0)

    let original_ref =  Cookies.get('handl_ref_domain')
    let this_domain = document.location.host
    let source = "Other";
    if (original_ref == '') {
        source = "Direct";
    }else if ( original_ref.match(/google\.co.*/i) !== null ){
        source = "Google";
    }else if ( original_ref.match(/bing\.co.*/i) !== null ){
        source = "Bing";
    }else if ( original_ref.match(/instagram\.co.*/i) !== null ){
        source = "Instagram";
    }else if ( original_ref.match(/facebook\.co.*/i) !== null || original_ref.match(/fb\.co.*/i) !== null ){
        source = "Facebook";
    }else if ( original_ref.match(/reddit\.com/i) !== null ){
        source = "Reddit";
    }else if ( original_ref.match(/twitter\.com/i) !== null || original_ref.match(/t\.co$/i) !== null || original_ref.match(/x\.com$/i) !== null ){
        source = "Twitter";
    }else if ( original_ref.match(/snapchat\.co.*/i) !== null ){
        source = "Snapchat";
    }else if ( original_ref.match(/youtube\.co.*/i) !== null ){
        source = "YouTube";
    }else if ( original_ref.match(/pinterest\.co.*/i) !== null ){
        source = "Pinterest";
    }else if ( original_ref.match(/linkedin\.co.*/i) !== null ){
        source = "LinkedIn";
    }else if ( original_ref.match(/tumblr\.com/i) !== null ){
        source = "Tumblr";
    }else if ( original_ref.match(/duckduckgo\.co.*/i) !== null ){
        source = "Duckduckgo";
    }else if ( original_ref.match(/openai\.com/i) !== null || original_ref.match(/chatgpt\.com/i) !== null ){
        source = "OpenAI";
    }else if ( original_ref.match(/perplexity\.ai/i) !== null ){
        source = "Perplexity";
    }else if ( original_ref.match(/claude\.ai/i) !== null ){
        source = "Claude";
    }else if ( original_ref.match(/gemini\.google\.com/i) !== null ){
        source = "Gemini";
    }else if ( original_ref.match(/copilot\.microsoft\.com/i) !== null ){
        source = "Copilot";
    }else if ( original_ref.match(/yelp\.com/i) !== null ){
        source = "Yelp";
    }else if (this_domain == original_ref){
        source = "Internal";
    }

    if(
        ( !Cookies.get('organic_source_str') || Cookies.get('organic_source_str') == '' ) ||
        ( Cookies.get('organic_source_str') && Cookies.get('organic_source_str') != '' && ['Internal','Direct'].indexOf(source) == -1 )
    ){
        SetRefLink('organic_source', document.referrer, true, 0)
        SetRefLink('organic_source_str', source, true, 0)
    }

    var qvars = getUrlVars()
    var googlePaidFromQuery =
        Object.keys(qvars).indexOf('gad_source') !== -1 ||
        Object.keys(qvars).indexOf('gad_campaignid') !== -1 ||
        Object.keys(qvars).indexOf('gclid') !== -1 ||
        Object.keys(qvars).indexOf('wbraid') !== -1 ||
        Object.keys(qvars).indexOf('gbraid') !== -1
    var googlePaidFromCookie =
        Cookies.get('gclid') !== undefined ||
        Cookies.get('wbraid') !== undefined ||
        Cookies.get('gbraid') !== undefined
    if (googlePaidFromQuery || googlePaidFromCookie) {
        SetRefLink('organic_source_str', 'Google', true, 0)
    }

    let traffic_source = 'Other'
    if (
        Cookies.get('gclid') != undefined ||
        Cookies.get('msclkid') != undefined ||
        Cookies.get('wbraid') != undefined ||
        Cookies.get('gbraid') != undefined ||
        Object.keys(qvars).indexOf('gclid') != -1 ||
        Object.keys(qvars).indexOf('msclkid') != -1 ||
        Object.keys(qvars).indexOf('wbraid') != -1 ||
        Object.keys(qvars).indexOf('gbraid') != -1 ||
        (Cookies.get('fbclid') != undefined && Cookies.get('_fbc') != undefined) ||
        (Object.keys(qvars).indexOf('fbclid') != -1 && Cookies.get('_fbc') != undefined) ||
        Object.keys(qvars).indexOf('gad_source') != -1 ||
        Object.keys(qvars).indexOf('gad_campaignid') != -1 
    ){
        traffic_source = 'Paid'
    }else if ( ['Google','Bing','Yahoo','Duckduckgo'].indexOf(Cookies.get('organic_source_str')) > -1 ){
        traffic_source = 'Organic'
    }else if ( ['Facebook','Twitter','Instagram','Snapchat','YouTube','Pinterest','LinkedIn','Tumblr','Reddit'].indexOf(Cookies.get('organic_source_str')) > -1 ){
        traffic_source = 'Social'
    }else if ( ['Internal','Direct'].indexOf(Cookies.get('organic_source_str')) > -1 ){
        traffic_source = 'Direct'
    }else if ( Cookies.get('organic_source_str') && ['Internal'].indexOf(Cookies.get('organic_source_str')) == -1 ){
        traffic_source = 'Referral'
    }

    SetRefLink('traffic_source', traffic_source, true, 0)
    SetRefLink('first_traffic_source', traffic_source, false, 0)

    document.dispatchEvent(new CustomEvent('handl_cookies_set'));

}

function SetRefLink(field, value, overwrite, count){
    // console.log(`Trying ${count} for ${field} ---> ${value}`)
    var domain = getDomainName()

    if (Cookies.get(field) != value) {
        if (Cookies.get(field) !== undefined && Cookies.get(field) != "" && !overwrite ){
            //bail...
            // console.log(`No need to update... ${field}`)
        }else if (Cookies.get(field) === undefined || Cookies.get(field) == '' || overwrite) {
            if (count == undefined) {
                count = 0
            }

            // utms_js[field] = value //not sure if we really need it here
            // console.log(`Setting cookies for ${field} as ${value}`)
            let cookieOptions = {
                domain: domain,
                sameSite: 'None',
                secure: true
            };

            if (!parseInt(handl_utm_session_cookies?.enabled)) {
                cookieOptions.expires = parseInt(handl_utm_cookie_duration[0]);
            }

            Cookies.set(field, value, cookieOptions);

        } else {
            count = count + 1
            if (count < 3) {
                setTimeout(function () {
                    SetRefLink(field, value, overwrite, count)
                }, 500)
            }
        }
    }
}

function get_url_domain(url) {
    let a      = document.createElement('a');
    a.href = url;
    return a.hostname;
}

//This is for OptinMonster events: https://optinmonster.com/docs/optinmonster-javascript-events-api/
document.addEventListener('om.Campaign.afterShow',function(){
    RunFieldFiller()
})

window.addEventListener('CookiebotOnAccept', function (e) {
    if (Cookiebot.consent.marketing)
    {
        RunHandL()
    }
}, false);

document.addEventListener("cmplz_enable_category", function(consentData) {
    var category = consentData.detail.category;
    if (category==='marketing'){
        RunHandL()
    }
});

// WP Consent API integration
document.addEventListener('wp_listen_for_consent_change', function (e) {
    var changedConsentCategory = e.detail;
    for (var key in changedConsentCategory) {
        if (changedConsentCategory.hasOwnProperty(key)) {
            if (key === 'marketing' && changedConsentCategory[key] === 'allow') {
                console.log('WP Consent API: Marketing consent granted, running HandL');
                RunHandL();
            }
        }
    }
});

// Check if consent already exists on page load
if (typeof wp_has_consent === 'function' && wp_has_consent('marketing')) {
    console.log('WP Consent API: Marketing consent already granted');
}


function afterBookingSelectDateAndTime(a){
    Object.keys(a.bookings[0].customFields).forEach(function(key) {
        let label = a.bookings[0].customFields[key].label
        if (Cookies.get(label)){
            a.bookings[0].customFields[key].value = Cookies.get(label)
        }
    });
}

window.ameliaActions = {
    beforeBooking: function(x,y,a){
        afterBookingSelectDateAndTime(a)
        x()
    }
}

/*
FormStack solution...
 */
function FF_AutoFiller(v){
    let it = jQuery(v)

    for ( vv of ['placeholder', 'title'] ) {
        var ph = it.attr(vv)
        var params = Object.values(handl_utm_all_params)
        var n = params.findIndex( (p) => new RegExp(p,'i').test(ph) )
        if (n > -1){
            var cval = Cookies.get(params[n])
            it.val(cval).attr('value',cval)
        }
    }
}
setTimeout(function () {
    jQuery('.fsCell.fsHidden>input').map( (i, v) => { FF_AutoFiller(v)  })
}, 3000)

jQuery(document).on('wsf-rendered', function(event, form_object, form_id, instance_id) {
    RunFieldFiller()
});

// function FF_OnAfterRender(){
//     console.log("Form rendered successfully!");
//     // var vars = ["Lead.UTM_Campaign__c","Lead.UTM_Medium__c","Lead.UTM_Source__c","Lead.UTM_Term__c","Lead.UTM_Content__c","Lead.GCLID__c","Lead.FBclid__c"]
//     // for (v of vars){
//     //     escape_v = v.replace(/\./g, '\\.')
//     //     jQuery("#"+escape_v).val(Cookies.get(v));
//     // }
//     return true;
// }

// wp.hooks.addFilter('jet.fb.inputs','jet-plugins',function(x,y,z,t,i){console.log(x,y,z,t,i)})
// wp.hooks.addFilter('jet.fb.macro.field.value','jet-plugins',function(x,y,z,t,i){console.log(x,y,z,t,i)})
// wp.hooks.addAction('jet.fb.observe.after','jet-plugins',function(x,y,z,t,i){console.log(x,y,z,t,i)})

//This is to support Jet Form Builder
jQuery(document).on('jet-form-builder/after-init',function(e){
    RunFieldFiller()
})

setTimeout(() => { //jotform iframe embed support
    if (typeof(window.utms_js) == 'object' && typeof(iframeParams) == 'object'){
        var iframeParams = [];
        iframeParams.push( jQuery.param(window.utms_js) )
        var src = ifr.src;
        ifr.src = src + "?" + iframeParams.join('&');
    }
}, "500");


jQuery( document ).on( 'elementor/popup/show' , function () {
    setTimeout(RunFieldFiller, 1000)
} );

//Borlabs cookie client-side
window.addEventListener('borlabs-cookie-consent-saved', () => {
    if (window.BorlabsCookie.Consents.hasConsent('handl-utm-grabber')){
        RunHandL()
    }
});

getAllHandLUTMParams = function() {
    var values = {};

    Object.values(handl_utm_all_params).forEach(function(param) {
        var value = Cookies.get(param);
        if (value) {
            values[param] = decodeURIComponent(value);
        }
    });

    return values;
};

function getUserData() {
    return {
        fbc: Cookies.get('_fbc') || '',
        fbp: Cookies.get('_fbp') || '',
        client_ip_address: Cookies.get('handl_ip') || '',
        client_user_agent: navigator.userAgent
    };
}

jQuery(document).ready(function($) {
    setTimeout(() => {
        var emailField = $('#email');
        var hasInitiatedCheckout = false;

        // Check if we're on the WooCommerce checkout page
        if ($('body').hasClass('woocommerce-checkout')) {
            emailField.on('blur', function() {
                if (!hasInitiatedCheckout && emailField.val().trim() !== '') {
                    hasInitiatedCheckout = true;
                    var userData = getUserData();
                    userData.em = [emailField.val()];
                    $.ajax({
                        url: handl_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'handl_initiate_checkout',
                            user_data: userData
                        },
                        success: function(response) {
                            console.log('InitiateCheckout event sent:', response);
                        }
                    });
                }
                });
            }
    }, 500);
});

function isFBCAPIEnabled() {
    return handl_ajax.fb_capi_enabled === '1';
}

jQuery(document).on('added_to_cart', function(event, fragments, cart_hash, button) {
    if (!isFBCAPIEnabled()) return;

    var productId = button.data('product_id');
    var quantity = button.data('quantity');

    if (typeof productId !== 'undefined' && typeof quantity !== 'undefined') {
        var userData = getUserData();

        jQuery.ajax({
            url: handl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handl_ajax_add_to_cart',
                product_id: productId,
                quantity: quantity,
                user_data: userData
            },
            success: function(response) {
                console.log('AddToCart event sent:', response);
            }
        });
    }
});


jQuery(function($) {
    $('form.checkout').on('checkout_place_order', function() {
        if (!isFBCAPIEnabled()) return;

        var userData = getUserData();

        $.ajax({
            url: handl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handl_add_payment_info',
                user_data: userData
            },
            success: function(response) {
                console.log('AddPaymentInfo event sent:', response);
            }
        });
    });
});

function sanitizeInput(input) {
    var element = document.createElement('div');
    element.innerText = input;
    return element.innerHTML;
}

function getConsentStatus() {
    if (!window.dataLayer) {
        console.warn('dataLayer is not defined.');
        return null;
    }
    let consent = null;
    // Iterate through dataLayer in reverse to find the latest consent event
    for (let i = window.dataLayer.length - 1; i >= 0; i--) {
        if (window.dataLayer[i][0] === 'consent' && window.dataLayer[i][1] === 'update') {
            consent = window.dataLayer[i][2];
            break;
        }else if (typeof window.dataLayer[i] === 'object' && window.dataLayer[i].value && window.dataLayer[i].value.event && window.dataLayer[i].value.event.includes('consent')) {
            consent = window.dataLayer[i].value;
            break;
        }
    }
    return consent;
}

function isConsentDenied() {
    const consentStatus = getConsentStatus();
    return consentStatus && (consentStatus.ad_user_data === 'denied' && (consentStatus.analytics_storage === 'denied' || !consentStatus.analytics_storage));
}

function handleConsentStatus() {
    if (isConsentDenied()) {
        RemoveHandLCookies();
    } else {
        RunHandL();
    }
}

function RemoveHandLCookies() {
    console.log('Removing HandL cookies');
    Object.values(handl_utm_all_params).forEach(param => {
        Cookies.remove(param, { path: '/', domain: getDomainName() });
    });
}

(function() {
    if (typeof handl_ajax.require_third_party_consent !== 'undefined' && handl_ajax.require_third_party_consent) {
        if (!window.dataLayer) {
            window.dataLayer = [];
        }

        const originalPush = window.dataLayer.push;

        window.dataLayer.push = function(...args) {
            args.forEach(entry => {
                if (
                    (Array.isArray(entry) && entry[0] === 'consent' && entry[1] === 'update')
                    || (entry.event && entry.event.includes('consent'))
                    || (entry.value && entry.value.event && entry.value.event.includes('consent'))
                ) {
                    // Handle the consent update
                    // console.log(`entry3: ${JSON.stringify(entry)}`);
                    handleConsentStatus();
                }

                // if (entry.event && entry.event.includes('marketing')) {
                //     // Handle the consent update
                //     console.log(`entry4: ${JSON.stringify(entry)}`);
                //     console.log(getConsentStatus());
                // }
                //
                // if (typeof entry === 'object' && Object.keys(entry).some(key => key.includes('marketing'))) {
                //     // Handle the consent update
                //     console.log(`entry5: ${JSON.stringify(entry)}`);
                //     console.log(getConsentStatus());
                // }
            });

            return originalPush.apply(this, args);
        };
    }
})();