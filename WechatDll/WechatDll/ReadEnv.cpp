#include "ReadEnv.h"
#include "stdafx.h"
#include <stdlib.h>
#include <stdio.h>

#define BUFSIZE 1024

using namespace std;

/*
* GetEnvironmentVariable
* via: https://docs.microsoft.com/en-us/windows/win32/procthread/changing-environment-variables#example-2
* 
* TODO: Define error codes and use "SetLastError" / GetLastError ?
*/
bool ReadEnvVariable(LPCTSTR varname, char* value, DWORD nSize)
{
	DWORD dwRet, dwErr;
	LPTSTR envData;
	BOOL fExist = {FALSE};

	envData = (LPTSTR)malloc(BUFSIZE * sizeof(TCHAR));
	if (NULL == envData)
	{
		printf_s("[Error] malloc: Out of memory\n");
		return FALSE;
	}

	dwRet = GetEnvironmentVariable(varname, envData, BUFSIZE);

	if (0 == dwRet)
	{
		dwErr = GetLastError();
		if (ERROR_ENVVAR_NOT_FOUND == dwErr)
		{
			printf_s("[Error] Environment variable does not exist.\n");
			fExist = FALSE;
		}
	}
	else if (BUFSIZE < dwRet)
	{
		envData = (LPTSTR)realloc(envData, dwRet * sizeof(TCHAR));
		if (NULL == envData)
		{
			printf_s("[Error] Realloc: Out of memory\n");
			return FALSE;
		}
		dwRet = GetEnvironmentVariable(varname, envData, dwRet);
		if (!dwRet)
		{
			printf_s("[Error] GetEnvironmentVariable failed (%d)\n", GetLastError());
			return FALSE;
		}
		else fExist = TRUE;
	}
	else fExist = TRUE;

	if (fExist) {
		DWORD sizeNeeded = WideCharToMultiByte(CP_UTF8, 0, envData, -1, NULL, 0, NULL, NULL);
		if (nSize >= sizeNeeded) {
			WideCharToMultiByte(CP_UTF8, NULL, envData, -1, value, sizeNeeded, NULL, NULL);
		}
		else {
			printf_s("[Error] nSize is Not Enough. sizeNeeded: %ld\n", sizeNeeded);
			return FALSE;
		}
		
	}

	free(envData);
	return fExist;
}
