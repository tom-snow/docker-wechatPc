#pragma once

#include <Windows.h>
#include <WinBase.h>
#include "Struct.h"

/*
* Read Environment Variable with get name
* bool ReadEnvVariable(
* [in] LPCTSTR varname,
* [out] CHAR* value,
* [in] DWORD nSize
* );
* 
* note: nSise is the CHAR* value length, it Shoud less than BUFSIZE(1024)
*/
bool ReadEnvVariable(LPCTSTR varname, char* value, DWORD nSize, bool ignoreNotFoundErr=false);
