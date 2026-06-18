function rpcEshopRequest(method, params, callback) {
    
    var linkTX = new sack();
    linkTX.requestFile = '/engine/rpc.php';
    linkTX.setVar('json', '1');
    linkTX.setVar('methodName', method);
    linkTX.setVar('params', json_encode(params));
    linkTX.method='POST';
    linkTX.onComplete = function() {
        linkTX.onHide();
        if (linkTX.responseStatus[0] == 200) {
            var resTX;
            try {
                resTX = eval('('+linkTX.response+')');
            } catch (err) { alert('Error parsing JSON output.\n Server returned:\n '+linkTX.response); }

            // First - check error state
            if (!resTX['status']) {
                // ERROR. Display it
                alert('Error ('+resTX['errorCode']+'): '+resTX['errorText']);
            } else {
                callback(resTX);
            }
        } else {
            alert('Server returned an error during HTTP request. Error code: '+linkTX.responseStatus[0]);
        }
    }
    linkTX.onShow();
    linkTX.runAJAX();
    
}
