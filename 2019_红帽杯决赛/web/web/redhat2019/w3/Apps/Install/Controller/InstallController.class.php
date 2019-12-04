<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 系统安装控制器 ]
*/

namespace Install\Controller;
use Think\Controller;
use Think\Storage;

class InstallController extends Controller{

    protected function _initialize(){
        $lock_data_dir = C('LOCK_DATA_DIR');
        if(Storage::has($lock_data_dir.'system_install.lock')){
            $this->error('系统已经成功安装，请不要重复安装!');
        }
    }

    //安装第一步，检测运行所需的环境设置
    public function step1(){
        session('error', false);

        //环境检测
        $this->env = check_env();

        //目录文件读写检测
        $this->dirfile = check_dirfile();

        //函数检测
        $this->func = check_func();

        //安装步骤
        session('step', 1);
        $this->display();
    }

    //安装第二步，创建数据库
    public function step2($db = null, $admin = null){
        if(IS_POST){
        /* 验证数据库信息 */
            $dbdata = I('post.db');
            $db = array();
            $db['DB_TYPE']   =   $dbdata['dbtype'];
            $db['DB_HOST']   =   $dbdata['dbhost'];
            $db['DB_USER']   =   $dbdata['dbuser'];
            $db['DB_PWD']    =   $dbdata['dbpasswd'];
            $db['DB_PORT']   =   $dbdata['dbport'];
            $db['DB_NAME']   =   $dbdata['dbname'];
            $db['DB_PREFIX'] =   $dbdata['dbprefix'];

            //验证db数据信息
            if(!empty($db)){
                foreach ($db as $key => $value) {
                    if(empty($value) && $key != 'DB_PWD'){
                        $this->error('请填写完整的数据库配置！');
                        break;
                    }
                }
            } else {
                $this->error('请填写完整的数据库配置！');
            }

            //验证DB_NAME
            if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/',$db['DB_NAME'])){
                $this->error('数据库名称格式不正确！');
            }

            //验证DB_PREFIX
            if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}$/',$db['DB_PREFIX'])){
                $this->error('数据表前缀格式不正确！');
            }
        /* end 验证数据库信息 */

        /* 验证用户中心数据库信息 */
            //验证用户中心信息
            $ucdata = I('post.uc');

            //验证用户中心DB_NAME
            if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/',$ucdata['ucdbname'])){
                $this->error('用户中心数据库名称格式不正确！');
            }

            //ucenter的数据库名称，不能与当前数据库名称相同
            if($ucdata['ucdbname'] == $db['DB_NAME']){
                $this->error('用户中心数据库名称不能与项目数据库名称一致！');
            }

            //验证用户中心DB_PREFIX
            if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}$/',$ucdata['ucdbprefix'])){
                $this->error('用户中心数据表前缀格式不正确！');
            }

            //验证用户中心域名
            if(!preg_match('/^http[s]?:\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/\?)|(\/[0-9a-zA-Z_!~\'\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/',$ucdata['ucdomain'])){
                $this->error('用户中心数据表前缀格式不正确！');
            }
        /* end 验证用户中心数据库信息 */    

        /* 验证用户信息 */
            //验证用户信息
            $admindata = I('post.admin');
            $username_len = mb_strlen($admindata['username']);
            if(empty($admindata['username']) || $username_len > 20 || $username_len < 5 || !preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9])+$/',$admindata['username'])){
                $this->error('用户名格式不正确！');
            }

            //验证用户密码
            $password_len = mb_strlen($admindata['password']);
            if(empty($admindata['password']) || $password_len > 20 || $password_len < 6 || $admindata['password'] != $admindata['passwordck']){
                $this->error('密码格式不正确！');
            }
        /* end 验证用户信息 */ 

            //验证数据库用户名密码是否正确
            try {
              //实例化PDO
              $_pdo = new \PDO($db['DB_TYPE'].':host='.$db['DB_HOST'], $db['DB_USER'], $db['DB_PWD']);
            } catch(\PDOException $_e) {
               $this->error($_e->getMessage());
            }

            //验证是否存在数据库文件
            $data_path = C('DATA_DIR');
            $filename = "install.sql";
            if(!is_file($data_path.$filename)){
                $this->error('数据库文件不存在！');
            }
            //获取数据
            $content = file_get_contents($data_path.$filename);
            $content = str_replace('$tableprefix_',$db['DB_PREFIX'],$content);

            //创建数据库
            $sql = "CREATE DATABASE IF NOT EXISTS `{$db['DB_NAME']}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $sth = $_pdo->prepare($sql);
            $res = $sth->execute();
            if(!$res){
                $this->error('创建数据库失败，请检查！');
            }
            
            //重新连接数据库，实例化并连接数据库
            try {
              //实例化PDO
              $_pdo = new \PDO($db['DB_TYPE'].':host='.$db['DB_HOST'].";dbname=".$db['DB_NAME'], $db['DB_USER'], $db['DB_PWD']);
            } catch(\PDOException $_e) {
               $this->error($_e->getMessage());
            }
            $sth = $_pdo->prepare($content);
            $res = $sth->execute();
            if(!$res){
                $this->error('导入数据库失败！');
            }

            //修改配置信息，设置管理员信息
            $update_sql = "UPDATE `".$db['DB_PREFIX']."adminuser` SET `username`='".$admindata['username']."',`password`='".sha1(md5($admindata['password']))."',`reg_ip`='".get_client_ip()."',`reg_time`='".time()."' WHERE id = 1;";
            
            //修改默认站点url
            $url = U(__SELF__,'',false,true);
            $urlinfo = parse_url($url);
            $new_url = $urlinfo['scheme'] . "://" . $urlinfo['host'] . $urlinfo['path'];
            $new_url = str_replace('/index.php','',$new_url);
            $update_sql .= "UPDATE `".$db['DB_PREFIX']."site` SET `url`='".$new_url."' WHERE id = 1;";

            //修改ucenter菜单
            $uc_url = parse_url($ucdata['ucdomain']);
            $update_sql .= "UPDATE `".$db['DB_PREFIX']."adminmenu` SET `url`='".U('Kgmanage/Login/index@'.$uc_url['host'].$uc_url['path'],'',false,true)."' WHERE id = 27 AND name='UCenter';";

            $sth = $_pdo->prepare($update_sql);
            $res = $sth->execute();

            //导入数据库完毕之后，创建数据库配置文件
            $dbconfig_str = "<?php\n\r";
            $dbconfig_str .= "/*\n\r";
            $dbconfig_str .= "* Author: [ Copy Lian ]\n\r";
            $dbconfig_str .= "* Date: [ ".date("Y.m.d")." ]\n\r";
            $dbconfig_str .= "* Description [ 数据库配置文件 ]\n\r";
            $dbconfig_str .= "*/\n\r\n\r";
            $dbconfig_str .= "return array(\n\r";

            //数据库信息
            $dbconfig_str .= "\t//数据库配置\n\r";
            foreach ($db as $key => $value) {
                if($key != 'DB_PORT'){
                    $dbconfig_str .= "\t" . "'".$key."' => '" . $value . "',\n\r";
                } else {
                    $dbconfig_str .= "\t" . "'".$key."' => " . $value . ",\n\r";
                }
            }
            $dbconfig_str .= "\n\r";

            //用户中心信息
            $ucenter_dsn_str =  $db['DB_TYPE'] . "://" . $db['DB_USER'] . ":" . $db['DB_PWD'] . "@" . $db['DB_HOST'] ."/" . $ucdata['ucdbname'];

            $dbconfig_str .= "\t//设置Ucenter\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DB_NAME' => '" . $ucdata['ucdbname'] . "',\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DB_PREFIX' => '" . $ucdata['ucdbprefix'] . "',\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DB_TABLE_MEMBERS' => 'Members',\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DB_TABLE_APPLICATIONS' => 'Applications',\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DB_DSN' => '" . $ucenter_dsn_str . "', //ucenterdsn\n\r";
            $dbconfig_str .= "\t" . "'UCENTER_DOMAIN' => '" . $ucdata['ucdomain'] . "', //用户中心的域名\n\r";

            $dbconfig_str .= ");\n\r?>";

            $ok = file_put_contents(CONF_PATH . "dbconfig.php", $dbconfig_str);
            if($ok){
                $lock_data_dir = C('LOCK_DATA_DIR');
                Storage::put($lock_data_dir.'system_install.lock', 'install lock.');
                $this->success('安装成功！',U('Index/complete'));
            } else {
                $this->error('写入数据库配置文件失败！');
            }

        } else {

            //检查是否有错误
            if(session('error')){
                $this->error('环境检测没有通过，请调整环境后重试！');
            }

            //验证步骤
            $step = session('step');
            if($step != 1 && $step != 2){
                $this->redirect('step1');
            }

            //安装步骤
            session('step', 2);
            $this->display();
        }
    }
}