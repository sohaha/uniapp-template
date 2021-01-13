export function useRequestPage(fn, p = {}, o = {}) {
  return apiUtil.useRequestPage(fn, p, o)
}

export function useRequestWith(fn, o = {}) {
  return apiUtil.useRequestWith(fn, o)
}

export function useRequest(fn, o = {}) {
  return apiUtil.useRequest(fn, o)
}

export const user = {
  demo(data) {
    return app.request.get('/ZlsManage/UserApi/demo.go?');
  },
  demoUpload() {
    return config.baseURL + '/ZlsManage/UserApi/upload.go';
  },
  current(data) {
    return ['get', '/ZlsManage/UserApi/UseriInfo.go', data];
  },
  userInfo: ['get', '/ZlsManage/UserApi/UseriInfo.go'],
  login(data) {
    return app.request.post('/ZlsManage/UserApi/GetToken.go', data);
  },
  logout() {
    return app.request.post('/ZlsManage/UserApi/ClearToken.go');
  },
  list(data) {
    return app.request.get('/ZlsManage/UserManageApi/UserLists.go', data);
  },
  uploadAvatar() {
    return config.baseURL + '/ZlsManage/UserApi/UploadAvatar.go';
  },
  updateUser(data) {
    return app.request.put('/ZlsManage/UserApi/Update.go', data);
  },
  createUser(data) {
    return app.request.post('/ZlsManage/UserManageApi/User.go', data);
  },
  deleteUser(data) {
    return app.request.delete('/ZlsManage/UserManageApi/User.go', data);
  },
  unreadMessageCount() {
    return app.request.get('/ZlsManage/UserApi/UnreadMessageCount.go');
  },
  updateMessageStatus(data) {
    return app.request.put('/ZlsManage/UserApi/MessageStatus.go', data);
  },
  sysUserLogs(data) {
    return app.request.get('/ZlsManage/UserApi/Logs.go', data);
  },
  editPassword(data) {
    return app.request.put('/ZlsManage/UserApi/EditPassword.go', data);
  },
  logs(data) {
    return app.request.get('/ZlsManage/SystemApi/SystemLogs.go', data);
  },
  logsDelete(data) {
    return app.request.delete('/ZlsManage/SystemApi/SystemLogs.go', data);
  },
  getSystemConfig(data) {
    return app.request.get('/ZlsManage/SystemApi/SystemConfig.go', data);
  },
  setSystemConfig(data) {
    return app.request.put('/ZlsManage/SystemApi/SystemConfig.go', data);
  },
  groupLists(data) {
    return app.request.get('/ZlsManage/UserManageApi/Groups.go', data);
  },
  createGroup(data) {
    return app.request.post('/ZlsManage/UserManageApi/Groups.go', data);
  },
  updateGroup(data) {
    return app.request.put('/ZlsManage/UserManageApi/Groups.go', data);
  },
  deleteGroup(data) {
    return app.request.delete('/ZlsManage/UserManageApi/Groups.go', data);
  },
  groupInfo(data) {
    return app.request.get('/ZlsManage/UserManageApi/GroupInfo.go', data);
  },
  ruleLists(data) {
    return app.request.get('/ZlsManage/UserManageApi/Rules.go', data);
  },
  addRule(data) {
    return app.request.post('/ZlsManage/UserManageApi/Rules.go', data);
  },
  editRule(data) {
    return app.request.put('/ZlsManage/UserManageApi/Rules.go', data);
  },
  updateUserRuleStatus(data) {
    return app.request.put('/ZlsManage/UserManageApi/UpdateUserRuleStatus.go', data);
  },
  deleteRule(data) {
    return app.request.delete('/ZlsManage/UserManageApi/Rules.go', data);
  }
};

export const sys = {
  sysUpdateGroupMenu(data) {
    return app.request.post('/ZlsManage/MenuApi/UpdateGroupMenu.go', data);
  },
  sysUserMenu(data) {
    return app.request.post('/ZlsManage/MenuApi/UserMenu.go', data);
  },
  sysMenuCreate(data) {
    return app.request.post('/ZlsManage/MenuApi/Create.go', data);
  },
  sysMenuDelete(data) {
    return app.request.post('/ZlsManage/MenuApi/Delete.go', data);
  },
  sysMenuUpdate(data) {
    return app.request.post('/ZlsManage/MenuApi/Update.go', data);
  },
  sysMenuSort(data) {
    return app.request.post('/ZlsManage/MenuApi/Sort.go', data);
  },
}

// 会员接口
export const member = {
  memberList() {
    return app.request.get('/Manage/MemberApi/list.go');
  },
  memberBanWxUser(data) {
    return app.request.post('/Manage/MemberApi/BanWxUser.go', data)
  }
}
