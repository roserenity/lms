function paymentHandler (data) {
  
  var scriptPattern = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;

  // Find all matches
  var scriptTags = data.match(scriptPattern);

  // Print or do something else with the script tags

  if(scriptTags) {
    var scriptContent = scriptTags[1].replace(/<script\b[^>]*>/i, "").replace(/<\/script>/i, "").replace(/\n|\r|\t/g, '');
    /*     console.log("content: ", scriptContent); */
    const vars = scriptContent.split("var")
    let newVars = vars.filter(item => item.trim() !== "");
    /*     console.log("vars: ", newVars);
    */
    let jsonStringStartIndex = newVars[0].indexOf('{');
    let jsonStringEndIndex = newVars[0].lastIndexOf('}');
    let paymentJsonString = newVars[0].substring(jsonStringStartIndex, jsonStringEndIndex + 1);
    let paymentDetails = JSON.parse(paymentJsonString);
    console.log(paymentDetails)
    let postUrl = newVars[1].match(/post_url\s*=\s*"([^"]+)"/)[1];
  
    var form = document.createElement('form');
    /* console.log(form) */
    form.setAttribute('action', postUrl);
    form.setAttribute('method', 'POST');
    form.setAttribute('id', 'form_submit');
    
    for (var key in paymentDetails) {
        /* console.log(key) */
        if (paymentDetails.hasOwnProperty(key)) {
            var input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', key);
            input.setAttribute('value', paymentDetails[key]);
            form.appendChild(input);
        }
    }
  
    var newWindow = window.open('', '_blank');
    newWindow.document.body.appendChild(form);
    //document.body.appendChild(form);
    try {
        form.submit();
    } catch (error) {
        console.log(error)
    } 
  }

}

export default {
  install(Vue) {
    Vue.prototype.$paymentHandler = paymentHandler
  }
}