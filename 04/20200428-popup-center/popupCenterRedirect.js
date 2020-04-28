function popupCenterRedirect(url, ratio, title) {
  
  ratio = (typeof(ratio) === 'number') ? ratio : 0.8
  title = (typeof(title) === 'string') ? title : '_blank'
  
  var w = screen.availWidth * ratio
  var h = screen.availHeight * ratio
  
  w = (w < 300) ? 300 : w
  h = (h < 300) ? 300 : h
  
  var userAgent = navigator.userAgent
  var mobile = function () {
    return /\b(iPhone|iP[ao]d)/.test(userAgent) ||
            /\b(iP[ao]d)/.test(userAgent) ||
            /Android/i.test(userAgent) ||
            /Mobile/i.test(userAgent)
  }
  var screenX = typeof window.screenX !== 'undefined' ? window.screenX : window.screenLeft
  var screenY = typeof window.screenY !== 'undefined' ? window.screenY : window.screenTop
  var outerWidth = typeof window.outerWidth !== 'undefined' ? window.outerWidth : document.documentElement.clientWidth
  var taskbarHeight = 22
  var outerHeight = typeof window.outerHeight !== 'undefined' ? window.outerHeight : document.documentElement.clientHeight - taskbarHeight
  var targetWidth = mobile() ? null : w
  var targetHeight = mobile() ? null : h
  var V = screenX < 0 ? window.screen.width + screenX : screenX
  var left = parseInt(V + (outerWidth - targetWidth) / 2, 10)
  var right = parseInt(screenY + (outerHeight - targetHeight) / 2.5, 10)
          
  var features = []
  if (targetWidth !== null) {
    features.push('width=' + targetWidth);
  }
  if (targetHeight !== null) {
    features.push('height=' + targetHeight);
  }
  features.push('left=' + left)
  features.push('top=' + right)

  var newWindow = window.open('', title, features.join(','))
 
  newWindow.document.write(`<script>window.moveTo(${left}, ${right});window.resizeTo(${targetWidth}, ${targetHeight})</script>`)
  newWindow.document.write(`<script>location.href="${url}"</script>`)
  
  if (window.focus) {
    newWindow.focus()
  }
}