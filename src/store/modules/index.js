/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-16 13:59:10
 */
// #ifndef VUE3
const files = require.context('.', false, /\.js$/);
const modules = {};

files.keys().forEach(key => {
  if (key === './index.js') return;
  modules[key.replace(/(\.\/|\.js)/g, '')] = files(key).default;
});


// #endif


// #ifdef VUE3
const files = import.meta.globEager('./*.js')
const modules = {};

Object.keys(files).forEach((key) => {
  if (key === './index.js') return;
  modules[key.replace(/(\.\/|\.js)/g, '')] = files[key].default 
})
// #endif

export default modules;