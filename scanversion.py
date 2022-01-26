#!/usr/bin/python3
import subprocess,time,os

def compute_version(ver):
    #计算微信版本号，并且转换为10进制字符串
    if len(ver) >= 7 :
        version_list = ver.split('.')

        #第一个最前面要补个6，后面几个则是补0
        version_list[0] = '6' + str(hex(int(version_list[0]))).split('x')[1]
        version_list[1] = str(hex(int(version_list[1]))).split('x')[1]
        if len(version_list[1]) == 1 :
            version_list[1] = '0' + version_list[1]
        version_list[2] = str(hex(int(version_list[2]))).split('x')[1]
        if len(version_list[2]) == 1 :
            version_list[2] = '0' + version_list[2]
        version_list[3] = str(hex(int(version_list[3]))).split('x')[1]
        if len(version_list[3]) == 1 :
            version_list[3] = '0' + version_list[3]
        version_hex = version_list[0] + version_list[1] + version_list[2] + version_list[3]
        version_int = int(version_hex,16)
        return str(version_int)
    else :
        return '0'

#死亡循环（有更好想法的可以提出），查找微信进程并且修改版本号
process_done_list = []
while True :
    #懒得装其他Python模块然后过滤了，所以直接调用系统的pgrep
    process_check = subprocess.run(['/usr/bin/pgrep','WeChat.exe'],capture_output=True,text=True)


    if len(process_check.stdout) > 0 :
        process_list = process_check.stdout.strip().split('\n')
        for process in process_list :
            #如果修改成功就不再修改了
            if not (process in process_done_list):
                dest_version_int = compute_version(os.environ['WECHAT_DEST_VERSION'])
                time.sleep(2)
                #调用scanmem修改版本号
                mod_process = subprocess.run(['/usr/bin/scanmem',process],input='write i32 116276c4 ' + dest_version_int,capture_output=True,text=True)
                print(mod_process.stdout)
                #为了保险，加了判断两个条件，scanmem返回值为0,并且内容不包含error才算修改成功
                if (mod_process.returncode == 0) and (not 'error:' in  mod_process.stdout):
                    process_done_list.append(process)
                    print(f'修改pid {process}微信版本号成功！')
                else:
                    print(f'修改pid {process}微信版本号失败...准备重试')
    time.sleep(2)
