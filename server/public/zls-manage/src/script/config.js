/**
 * Created by 影浅-seekwe@gmail.com on 2018-11-19
 */
var apiUrl = '';
var title = '后台管理系统';
var messagesRegularly = 0;
var apis = {
  login: ['post', '/ZlsManage/UserApi/GetToken.go'],
  logout: ['post', '/ZlsManage/UserApi/ClearToken.go'],
  sysUseriInfo: ['get', '/ZlsManage/UserApi/UseriInfo.go'],
  sysEditPassword: ['put', '/ZlsManage/UserApi/EditPassword.go'],
  sysUserLists: ['get', '/ZlsManage/UserManageApi/UserLists.go'],
  sysGroupLists: ['get', '/ZlsManage/UserManageApi/Groups.go'],
  sysCreateGroup: ['post', '/ZlsManage/UserManageApi/Groups.go'],
  sysUpdateGroup: ['put', '/ZlsManage/UserManageApi/Groups.go'],
  sysDeleteGroup: ['delete', '/ZlsManage/UserManageApi/Groups.go'],
  sysGroupInfo: ['get', '/ZlsManage/UserManageApi/GroupInfo.go'],
  sysRuleLists: ['get', '/ZlsManage/UserManageApi/Rules.go'],
  sysAddRule: ['post', '/ZlsManage/UserManageApi/Rules.go'],
  sysDeleteRule: ['delete', '/ZlsManage/UserManageApi/Rules.go'],
  EditRule: ['put', '/ZlsManage/UserManageApi/Rules.go'],
  sysUpdateUserRuleStatus: ['put', '/ZlsManage/UserManageApi/UpdateUserRuleStatus.go'],
  sysUploadAvatar: '/ZlsManage/UserApi/UploadAvatar.go',
  sysUpdateUser: ['put', '/ZlsManage/UserApi/Update.go'],
  sysCreateUser: ['post', '/ZlsManage/UserManageApi/User.go'],
  sysDeleteUser: ['delete', '/ZlsManage/UserManageApi/User.go'],
  sysUserLogs: ['get', '/ZlsManage/SystemApi/Logs.go'],
  systemLogs: ['get', '/ZlsManage/SystemApi/SystemLogs.go'],
  systemLogsDelete: ['delete', '/ZlsManage/SystemApi/SystemLogs.go'],
  sysGetSystemConfig: ['get', '/ZlsManage/SystemApi/SystemConfig.go'],
  sysSetSystemConfig: ['put', '/ZlsManage/SystemApi/SystemConfig.go'],
  sysUnreadMessageCount: ['get', '/ZlsManage/SystemApi/UnreadMessageCount.go'],
  sysUpdateMessageStatus: ['put', '/ZlsManage/SystemApi/MessageStatus.go'],
  // 会员接口
  memberList:['get','/Manage/MemberApi/list.go'],
  memberBanWxUser:['post','/Manage/MemberApi/BanWxUser.go'],
};
