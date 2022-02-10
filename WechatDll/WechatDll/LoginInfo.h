#pragma once

#include "Config.h"
#include "Struct.h"
#include "Package.h"


bool readConfigFile(const char * cfgfilepath, const string & key, string & value);

VOID SendWechatUser(Package *package = NULL);
// 读取登录的用户详情
VOID ReadWechatUser();
VOID WriteData();
