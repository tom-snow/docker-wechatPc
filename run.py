#!/usr/bin/python3
import sys, subprocess, os

def run_php():
    subprocess.run(['/usr/bin/php7.2','index.php','start'],
        cwd='/ServerPhp')
    subprocess.Popen(['/usr/bin/tail','-f','/ServerPhp/Storage/logs/wechat.log'])

def run_scanversion():
    subprocess.Popen(['/usr/bin/python3','/scanversion.py'])

def run_vnc():
    #根据VNCPASS环境变量生成vncpasswd文件
    os.makedirs('/root/.vnc', mode=755, exist_ok=True)
    passwd_output = subprocess.run(['/usr/bin/vncpasswd','-f'],input=os.environ['VNCPASS'].encode(),capture_output=True)
    with open('/root/.vnc/passwd', 'wb') as f:
        f.write(passwd_output.stdout)
    os.chmod("/root/.vnc/passwd", 0o700)
    subprocess.Popen(['/usr/bin/vncserver','-localhost',
        'no', '-xstartup', '/usr/bin/openbox' ,':5'])

def run_hook():
    app_id = os.environ["APP_ID"]
    app_key = os.environ["APP_KEY"]
    #修改配置文件
    subprocess.run(['sed', '-i', '-e',
        f's@api_id=.*$@app_id={app_id}@g' , '-e',
        f's@api_key=.*$@app_key={app_key}@g',
        '/Debug/Config.txt'])
    subprocess.run(['wine','/Debug/WechatRobot.exe'])

def run_all_in_one():

    run_php()
    run_scanversion()
    run_vnc()
    run_hook()


if __name__ == "__main__" :
   print("All in one 微信Hook容器")
   run_all_in_one()
