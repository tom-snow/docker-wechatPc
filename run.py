#!/usr/bin/python3
import sys, subprocess, os, signal, datetime


class DockerWechatHook:
    def __init__(self):
        signal.signal(signal.SIGINT, self.now_exit)
        signal.signal(signal.SIGHUP, self.now_exit)
        signal.signal(signal.SIGTERM, self.now_exit)

    def now_exit(self,signum, frame):
        self.exit_container()

    def run_php(self):
        if os.path.exists('/ServerPhp/Storage/pid/wechat.pid'):
            os.remove('/ServerPhp/Storage/pid/wechat.pid')
        if (os.environ['PHPDEBUG'] == 'false') or (os.environ['PHPDEBUG'] == 'False'):
            subprocess.run(['sed', '-i', "s@debug' => true@debug' => false@g", '/ServerPhp/Config/Config.php'])
        self.php = subprocess.run(['/usr/bin/php7.2','index.php','start'], cwd='/ServerPhp')

    def run_scanversion(self):
        self.scanversion = subprocess.Popen(['/usr/bin/python3','/scanversion.py'])

    def run_vnc(self):
        #根据VNCPASS环境变量生成vncpasswd文件
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
        #修改配置文件
        subprocess.run(['sed', '-i', '-e',
            f's@api_id=.*$@app_id={app_id}@g' , '-e',
            f's@api_key=.*$@app_key={app_key}@g',
            '/Debug/Config.txt'])
        self.hook = subprocess.run(['wine','/Debug/WechatRobot.exe'])

    def exit_container(self):
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 正在退出容器...')
        try:
            print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 退出修改版本脚本...')
            os.kill(self.scanversion.pid, signal.SIGTERM)
        except:
            pass
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
        self.run_scanversion()
        self.run_vnc()
        self.run_hook()
        print(datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')+ ' 启动完成.')


if __name__ == '__main__' :
    print('---All in one 微信Hook容器---')
    hook = DockerWechatHook()
    hook.run_all_in_one()
