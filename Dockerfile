FROM docker.io/zixia/wechat
MAINTAINER endokai

USER root
WORKDIR /

ENV VNCPASS=YourSafeVNCPassword \
    APP_ID=CD7160A983DD8A288A56BAA078780FCA \
    APP_KEY=F2B283D51B3F4A1A4ECCB7A3620E7740 \ 
    WECHAT_DEST_VERSION=3.3.0.115 \
    WINEPREFIX=/home/user/.wine \
    LANG=zh_CN.UTF-8 \
    LC_ALL=zh_CN.UTF-8 \
    DISPLAY=:5 \
    PHPDEBUG=true


EXPOSE 5678 5905  8686

#VOLUME [\
#  "/home/user/WeChat Files", \
#  "/home/user/.wine/drive_c/users/user/Application Data" \
#]

COPY Bin/Debug /Debug
COPY ServerPhp /ServerPhp
COPY run.py /run.py
COPY scanversion.py /scanversion.py
COPY wine/Tencent /Tencent
COPY wine/微信.lnk /home/user/.wine/drive_c/users/Public/Desktop/微信.lnk
COPY wine/system.reg  /home/user/.wine/system.reg
COPY wine/user.reg  /home/user/.wine/user.reg
COPY wine/userdef.reg /home/user/.wine/userdef.reg


RUN apt update &&  \
  apt install wget -y && \
  apt autoremove -y && \
  apt clean && \
  rm -fr /tmp/*

RUN wget https://packages.sury.org/php/apt.gpg && \
    apt-key add apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.list && \
    apt update && \
    apt install php7.2-cli scanmem wget winbind samba tigervnc-standalone-server tigervnc-common openbox -y && \
    wget --no-check-certificate -O /bin/dumb-init "https://github.com/Yelp/dumb-init/releases/download/v1.2.5/dumb-init_1.2.5_x86_64"  && \
    chmod a+x /bin/dumb-init && \
    chmod a+x /run.py && \
    chmod a+x /scanversion.py && \
    cp -rf /Tencent "/home/user/.wine/drive_c/Program Files/" && \
    chown root:root -R /home/user/.wine && \
    rm -rf /Tencent


ENTRYPOINT [ "/bin/dumb-init" ]
CMD ["/run.py","start"]
