// JavaScript Document

// websocket句柄
var ws = null;

// 获取登录信息
function getLoginInfo(wechatId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_LOGIN_INFO);
	json.setBody({});
	send(json.getJson());
}
// 获取登录状态
function getLoginStatus(wechatId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_WECHAT_GET_LOGIN_STATUS);
	json.setBody({});
	send(json.getJson());
}

// 退出登录
function logout(wechatId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_WECHAT_QUIT);
	json.setBody({});
	send(json.getJson());
}

// 发送文本消息
function sendText(wechatId, wxid, content)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_MESSAGE_SEND_TEXT);
	json.setBody({
		wxid: wxid,
		content: content
	});
	send(json.getJson());
}
// 发送图片消息
function messageSendImage(wechatId, wxid, base64Content)
{
	/**
	 * 参数说明：
	 * base64Content: 含 mime_type 信息的 base64 加密图片。(其中 mime_type 必须)【后端通过正则(/(?<=^data:)\w+\/[\w\-\+\d.]+(?=;base64,)/i)判断 mime_type】
	 * 		格式形如： data:image/png;base64,aW1hZ2U=
	 */
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_MESSAGE_SEND_IMAGE);
	json.setBody({
		wxid: wxid,
		base64Content: base64Content
	});
	send(json.getJson());
}
// 发送附件消息
function messageSendFile(wechatId, wxid, base64Content, fileName)
{
	/**
	 * 参数说明：
	 * base64Content: 含 mime_type 信息的 base64 加密文件。(其中 mime_type 可以不准确，但是需要符合格式)【后端通过正则(/(?<=^data:)\w+\/[\w\-\+\d.]+(?=;base64,)/i)判断 mime_type】
	 * 		格式形如： data:application/json;base64,e30=
	 * fileName: 文件名(不含路径)，微信客户端上会显示此文件名
	 */
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_MESSAGE_SEND_FILE);
	json.setBody({
		wxid: wxid,
		base64Content: base64Content,
		fileName: fileName
	});
	send(json.getJson());
}
// 发送名片消息
function messageSendCard(wechatId, wxid, xml)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_MESSAGE_SEND_CARD);
	json.setBody({
		wxid: wxid,
		xml: xml
	});
	send(json.getJson());
}
// 发送xml消息
function messageSendXml(wechatId, type, wxid, fromWxid, imageUrl, xml)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_MESSAGE_SEND_XML);
	json.setBody({
		type: type,
		wxid: wxid,
		fromWxid: fromWxid,
		imageUrl: imageUrl,
		xml: xml
	});
	send(json.getJson());
}
// wxid加好友
function friendAdd(wechatId, wxid, message)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_ADD);
	json.setBody({
		wxid: wxid,
		message: message
	});
	send(json.getJson());
}
// v1加好友
function friendAddFromV1(wechatId, v1, message)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_ADD_FROM_V1);
	json.setBody({
		v1: v1,
		message: message
	});
	send(json.getJson());
}
// 删除好友
function friendDelete(wechatId, wxid)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_DELETE);
	json.setBody({
		wxid: wxid
	});
	send(json.getJson());
}
// v1+v2同意好友请求
function friendVerify(wechatId, v1, v2)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_VERIFY);
	json.setBody({
		v1: v1,
		v2: v2
	});
	send(json.getJson());
}
// 好友列表
function friendList(wechatId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_LIST);
	json.setBody({});
	send(json.getJson());
}
// 创建群聊
function roomCreate(wechatId, wxid1, wxid2)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_CREATE);
	json.setBody({
		wxid1: wxid1,
		wxid2: wxid2
	});
	send(json.getJson());
}
// 修改好友备注
function setFriendRemark(wechatId, wxid, remark)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_FRIEND_REMARK);
	json.setBody({
		wxid: wxid,
		remark: remark
	});
	send(json.getJson());
}
// 修改群名称
function editRoomName(wechatId, roomId, roomName)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_EDIT_NAME);
	json.setBody({
		roomId: roomId,
		roomName: roomName
	});
	send(json.getJson());
}
// 发送群公告
function setRoomAnnouncement(wechatId, roomId, announcement)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_ANNOUNCEMENT);
	json.setBody({
		roomId: roomId,
		announcement: announcement
	});
	send(json.getJson());
}
// 获取群成员列表
function roomMemberList(wechatId, roomId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_MEMBER_LIST);
	json.setBody({
		roomId: roomId
	});
	send(json.getJson());
}
// 拉好友入群
function roomAddMember(wechatId, roomId, wxid)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_ADD_MEMBER);
	json.setBody({
		roomId: roomId,
		wxid: wxid
	});
	send(json.getJson());
}
// 删除群成员
function roomDeleteMember(wechatId, roomId, wxid)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_DELETE_MEMBER);
	json.setBody({
		roomId: roomId,
		wxid: wxid
	});
	send(json.getJson());
}
// 艾特群成员
function roomAtMember(wechatId, roomId, wxid, nickname, message)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_AT_MEMBER);
	json.setBody({
		roomId: roomId,
		wxid: wxid,
		nickname: nickname,
		message: message
	});
	send(json.getJson());
}
// 退出群聊
function roomQuit(wechatId, roomId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_ROOM_QUIT);
	json.setBody({
		roomId: roomId
	});
	send(json.getJson());
}
// 收款
function transferRecv(wechatId, wxid, transferId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_TRANSFER_RECV);
	json.setBody({
		roomId: roomId,
		transferId: transferId
	});
	send(json.getJson());
}
// 关闭一个微信
function closeWechat(wechatId)
{
	var json = new Package();
	json.setWechatId(wechatId);
	json.setOpCode(OPCODE_WECHAT_QUIT);
	json.setBody({});
	send(json.getJson());
}
// 新开一个微信
function openWechat()
{
	var json = new Package();
	json.setWechatId('1234567890ABCDEFGHIJKLMNOPQRSTUV');
	json.setOpCode(OPCODE_WECHAT_OPEN);
	json.setBody({});
	send(json.getJson());
}
// 发送
function send(msg)
{
	if (ws.status == true) {
		ws.send(msg);
	}
}
// 接收消息
function recv(data)
{
	var json = new Package();
	json.setJson(data);
	var wechatId = json.getWechatId();
	var opCode = json.getOpCode();
	var body = json.getBody();
	switch (opCode) {
		// 收到微信消息
		case OPCODE_MESSAGE_RECEIVE:
			if (body.msgType == 3) {
				if (body.imageFile.status == true) {
					body.content = '<img src="'+body.imageFile.base64Content+'"/>';
				} else {
					body.content = body.imageFile.message;
				}
			}
			recvMessage(wechatId, body);
		break;
		// 好友列表
		case OPCODE_FRIEND_LIST:
			var pageTotal = Math.ceil(body.total / body.pageSize);
			recvFriendList(wechatId, body.friendList, body.page, pageTotal)
		break;
		// 登录状态
		case OPCODE_WECHAT_GET_LOGIN_STATUS:
			// 登录成功
			if (body.loginStatus != 0) {
				hideQrcode();  // 关闭二维码显示
				getLoginInfo(wechatId);  // 获取登录详情
				setTimeout(function(){
					friendList(wechatId);  // 获取好友列表
				}, 1000);
			}
		break;
		// 登录二维码
		case OPCODE_WECHAT_QRCODE:
			if (body.loginQrcode) {
				// 显示二维码
				showQrcode(body.loginQrcode);
			}
		break;
		// 已登录的账号信息
		case OPCODE_LOGIN_INFO:
			// 显示登录账号信息
			loginSuccess(wechatId, body);
		break;
	}
	//{"wechatId":"E15B66F22D6D0178791EB1864BA49375","opCode":18,"body":{"login_status":1}}
//{"wechatId":"E15B66F22D6D0178791EB1864BA49375","opCode":18,"body":{"wxid":"wxid_vju0phxgdhgp22","username":"supper-busy","nickname":"\u6211ddd\u662f\u7279\u6b8a\u7684\u4f60d\u59b9\u59b9\u7684\u5431mdjdjfff","headUrl":"http:\/\/wx.qlogo.cn\/mmhead\/ver_1\/rz5dWiaYy1q6pZR3FlSmaI27IYY7FpqPcsne6eOw35f0l1XpTF1s8qZJOkDJAxdlWh9z7qqdSrm7Ur6mtCQYEyzsu20IA6lPib5y4Z6qyOX54\/0","mobile":"13711458538","email":"chengciming@vip.qq.com","sex":1,"nation":"CN","province":"Guangdong","city":"Guangzhou","sign":"\u600e\u4e48\u6837\u7684\u4eba\u5c31\u6709\u600e\u4e48\u6837\u7684\u6545\u4e8b\u3002\u3002\u3002","device":"android"}}
	
}

function connect()
{
	/*
	* 使用 app_id, timestamp 与 app_key 生成 sha256 hash：
	* hash = sha256("app_id={app_id}&timestamp={timestamp}&app_key={app_key}");
	* 	其中 app_id 与 app_key 需与 php 端相同（docker 环境下环境变量会覆盖 config.php 设置），timestamp 是当前时间，需要与 php 服务器端误差小于 config.php 中 expire 变量的值（默认10分钟），如果 websocket 连接失败可能是时区不同，请自行统一
	* 	【求 hash 时，各参数顺序不能乱！】
	* 再按照 app_id, timestamp 与 hash 拼接成 websocket 的 query，如：
	* app_id=1234567890ABCDEFGHIJKLMNOPQRSTUV&timestamp=1646320498300&hash=657224060800afa42f6941a0c988e0d0a8cc6a2b322672f6733392b39ef78a77
	* 最后链接就为： ws://127.0.0.1:5678/?app_id=1234567890ABCDEFGHIJKLMNOPQRSTUV&timestamp=1646320498300&hash=657224060800afa42f6941a0c988e0d0a8cc6a2b322672f6733392b39ef78a77
	*/
	var query = "";
	var app_id = "1234567890ABCDEFGHIJKLMNOPQRSTUV";
	var app_key = "1234567890ABCDEFGHIJKLMNOPQRSTUV";
	var wsUrl = "ws://127.0.0.1:5678/?";
	var timestamp = Date.now();
	query += "app_id=" + app_id;
	query += "&timestamp=" + timestamp;
	var hash = sha256(query + "&app_key=" + app_key);
	query += "&hash=" + hash;
	wsUrl += query;

	// console.log(wsUrl);
	// 打开一个 web socket
	ws = new WebSocket(wsUrl);
	ws.status = false;
	ws.onopen = function() {
		ws.status = true;
		$('.network_notice').hide();
	};
	ws.onmessage = function (evt) {
		recv(evt.data);
	};
	ws.onclose = function() {
		ws.status = false;
		$('.network_notice').show();
		setTimeout(connect, 3000);
	};
}
$(function (){
	if ("WebSocket" in window) {
	   //alert("您的浏览器支持 WebSocket!");
	   connect();
	} else {
	   // 浏览器不支持 WebSocket
	   alert("您的浏览器不支持 WebSocket，请使用谷歌浏览器!");
	}
})