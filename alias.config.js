/**
 * @Author: seekwe
 * @Date:   2019-11-11 15:04:12
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-16 13:58:40
 */

const path = require('path');
module.exports = {
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      _c: path.resolve(__dirname, 'src/components')
    }
  }
};
