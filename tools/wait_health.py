"""Wait for Apache (and optionally the health endpoint) after container startup."""
import argparse
import subprocess
parser=argparse.ArgumentParser()
parser.add_argument('--container')
parser.add_argument('--socket-only',action='store_true')
args=parser.parse_args()
code='''$ready=false;for($i=0;$i<60;$i++){$s=@fsockopen('127.0.0.1',80,$errno,$error,1);if($s){fclose($s);$ready=true;break;}usleep(500000);}if(!$ready)exit(1);'''
if not args.socket_only:
 code+='''$ready=false;for($i=0;$i<30;$i++){$body=@file_get_contents('http://localhost/health');$data=$body!==false?json_decode($body,true):null;if(($data['ok']??false)===true){$ready=true;break;}usleep(500000);}if(!$ready)exit(1);echo "Aplicacao pronta\\n";'''
command=['docker','exec',args.container] if args.container else ['docker','compose','exec','-T','app']
subprocess.run(command+['php','-r',code],check=True)
