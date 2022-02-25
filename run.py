#!/usr/bin/python3
import subprocess, os, signal, datetime


class DockerWechatHook:
    def __init__(self):
        signal.signal(signal.SIGINT, self.now_exit)
        signal.signal(signal.SIGHUP, self.now_exit)
        signal.signal(signal.SIGTERM, self.now_exit)

    def compute_version(self, ver):
        # 计算微信版本号，并且转换为10进制字符串
        if len(ver) >= 7 :
            version_list = ver.split('.')

            # 第一个最前面要补个6，后面几个则是补0
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
            version_hex = "0x" + version_list[0] + version_list[1] + version_list[2] + version_list[3]
            # version_int = int(version_hex, 16)
            return str(version_hex)
        else :
            return '0x63030073' # 默认 3.3.0.115

    def now_exit(self,signum, frame):
        self.exit_container()

    def run_php(self):
        app_id = os.environ['APP_ID']
        app_key = os.environ['APP_KEY']
        subprocess.run(['sed', '-i', '-e', 
            f"s@app_id' => '.*'@app_id' => '{app_id}'@g", '-e', 
            f"s@app_key' => '.*'@app_key' => '{app_key}'@g", 
            '/ServerPhp/Config/Config.php'])
        if os.path.exists('/ServerPhp/Storage/pid/wechat.pid'):
            os.remove('/ServerPhp/Storage/pid/wechat.pid')
        if os.environ['PHPDEBUG'].lower() == 'false':
            subprocess.run(['sed', '-i', "s@debug' => true@debug' => false@g", '/ServerPhp/Config/Config.php'])
        self.php = subprocess.Popen(['/usr/bin/php7.2','index.php','start'], cwd='/ServerPhp')

    def run_vnc(self):
        # 根据VNCPASS环境变量生成vncpasswd文件
        os.makedirs('/root/.vnc', mode=755, exist_ok=True)
        passwd_output = subprocess.run(['/usr/bin/vncpasswd','-f'],input=os.environ['VNCPASS'].encode(),capture_output=True)
        with open('/root/.vnc/passwd', 'wb') as f:
            f.write(passwd_output.stdout)
        os.chmod('/root/.vnc/passwd', 0o700)
        self.vnc = subprocess.Popen(['/usr/bin/vncserver','-localhost',
            'no', '-xstartup', '/usr/bin/openbox' ,':5'])

    def run_hook(self):
        app_id = os.environ['APP_ID']
        app_key = os.environ['APP_KEY']
        hex_version = self.compute_version(os.environ['WECHAT_DEST_VERSION'])
        os.environ['WECHAT_HEX_VERSION'] = hex_version
        # 修改配置文件
        subprocess.run(['sed', '-i', '-e',
            f's@app_id=.*$@app_id={app_id}@g' , '-e',
            f's@app_key=.*$@app_key={app_key}@g','-e',
            f's@hex_version=.*$@hex_version={hex_version}@g',
            '/Debug/Config.txt'])
        # subprocess.run(['cp','/Debug/Config.txt', '/home/user/.wine/drive_c/Config.txt'])
        self.hook = subprocess.run(['wine','/Debug/WechatRobot.exe'])

    def exit_container(self):
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 正在退出容器...')
        try:
            print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 退出Hook程序...')
            os.kill(self.hook.pid, signal.SIGTERM)
        except:
            pass
        try:
            print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 退出VNC...')
            os.kill(self.vnc.pid, signal.SIGTERM)
        except:
            pass
        try:
            print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 退出PHP...')
            os.kill(self.php.pid, signal.SIGTERM)
        except:
            pass
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 已退出容器.')

    def run_all_in_one(self):
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 启动容器中...')
        self.run_php()
        self.run_vnc()
        self.run_hook()
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 启动完成.')


if __name__ == '__main__' :
    print('---All in one 微信Hook容器---')
    hook = DockerWechatHook()
    hook.run_all_in_one()
