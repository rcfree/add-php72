function copyToClipboard(s) {
    if (window.clipboardData && clipboardData.setData) {
    	clipboardData.setData('text', $(s).text());
    }
    else{
    	ADD.Notice.showAlert("Copy error","This feature will only work in Internet Explorer.","OK");
    }
}