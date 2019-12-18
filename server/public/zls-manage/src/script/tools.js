console.warn('load: tools.js');

window['arrayAdd'] = function (arr, v) {
  var i = arr.indexOf(v);
  if (i < 0) {
    arr.push(v);
  }
};

window['arrayReduce'] = function (arr, v) {
  var i = arr.indexOf(v);
  if (i >= 0) {
    arr.splice(i, 1);
  }
};
