<?php

class FaceAction extends Action {
  public function import(){
		$photo = $this->read_dir("./static/face/");
		foreach($photo as $key => $value){
			echo $key.' '.$value['src'].'<br/>';
			D('Facephoto')->add(array('src'=>$value['src']));
		}
		
	}
	public function read_dir($dir) {
		$i = 0;
		if(is_dir($dir)) {
			if ($path = opendir($dir)) {
				while (false !== ($file = readdir($path))) {
					$i++;
					echo $file . "<br/>";
					$result[$i]['src']=$file;
					if((is_dir($dir."/".$file)) && $file!="." && $file!="..") {
						read_dir($dir."/".$file . '/');
					} else {
						if($file!="." && $file!="..") {
							$diename = str_replace('//', '/', $dir . $file);
							file_put_contents('treeList.txt', $diename . "\n", FILE_APPEND);
						 }
					}
				}
				closedir($path);
			}
		}
		
		return $result;
	}
	public function face(){
		$gender = $this->_get('gender','intval');
		$college = $this->_get('college','intval');
		$rank = $this->_get('rank','intval');
		$order = $this->_get('order','intval');
		if($gender){
			$where['gender']= "WHERE gender=".$gender;
		}
		if($college){
			$where['college']=" AND school=".$college;
		}
		if($order==1){
			$where['order']= " ORDER BY Facephoto.likes DESC";
		}
		if($rank){
			$where['rank']= " LIMIT 0,".$rank;
		}
		$sql = "SELECT distinct *,
				Facephoto.id AS id, Facephoto.likes AS likes 
				FROM Facephoto
				LEFT JOIN Facename ON Facephoto.nid = Facename.id".
				$where['gender'].$where['college'].
				$where['order'].
				$where['rank']."";
		$photos=M ()->query ( $sql );
		$photo = array_slice($photos, 0, 19, true);
		$pic = $this->parse($photo,1);
		$this->assign('p',$pic);
		$this->display();
	}
	public function data(){
		header("Content-Type:text/html; charset=utf-8");
		$upper = $this->_get('upper','intval');
		$lower = $this->_get('lower','intval');
		$gender = $this->_get('gender','intval');
		$college = $this->_get('college','intval');
		$rank = $this->_get('rank','intval');
		$order = $this->_get('order','intval');
		if(!$lower){
			$lower=20;
			$upper=$lower+50;
		}
		if($gender){
			$where['gender']= "WHERE gender=".$gender;
		}
		if($college){
			$where['college']=" AND school=".$college;
		}
		if($order==1){
			$where['order']= " ORDER BY Facephoto.likes DESC";
		}
		if($rank){
			$where['rank']= " LIMIT ".$lower.",".$rank;
		}	else	{
			$where['rank']= " LIMIT ".$lower.",".$upper;
		}
		$sql = "SELECT distinct *,
				Facephoto.id AS id, Facephoto.likes AS likes 
				FROM Facephoto
				LEFT JOIN Facename ON Facephoto.nid = Facename.id".
				$where['gender'].$where['college'].
				$where['order'].
				$where['rank']."";
		$photo=M ()->query ( $sql );
		$pic = $this->parse($photo,0);
		$data = json_encode($pic);
		echo urldecode(stripslashes($data));
	}
	function parse($pic,$type){
		foreach($pic as $key=>$value){
			$school=M('School')->where('id='.$value['school'])->find();
			if($type==1){
				$pic[$key]['name']=$value['name'];
			}	else	{
				$pic[$key]['name']=urlencode($value['name']);
			}
			$pic[$key]['college']=$school['englishname'];
			$pic[$key]['src']='http://localhost/static/face/images/'.$value['src'];
			$pic[$key]['link']="#";			
		}
		return $pic;
	}
	public function vote(){
		if($pid = $this->_get ( 'pid', 'intval' )){
			if($nid = M('Facephoto')->where('id='.$pid)->find()){
				echo $nid['nid'];
				$data = array('nid'=>$nid['nid'],'pid'=>$pid,'ip'=>$_SERVER["REMOTE_ADDR"],'timeline'=>time());
				if(M('Facevote')->add($data)){
					$return = 1;
					$msg="添加成功";
					M('Facephoto')->where('id='.$data['pid'])->setField(array('likes'=>($nid['likes']+1)));
					$name = M('Facename')->where('id='.$data['nid'])->find();
					M('Facename')->where('id='.$data['nid'])->setField(array('likes'=>($name['likes']+1)));
				}	else	{
					$return=0;
					$msg="添加失败";
				}
			}
		}
		
		if ($this->isAjax ()) {
			$this->ajaxReturn ( array (), $msg, $return );
		} else {
			if ($return) {
				$this->success ( $msg );
				exit ();
			} else {
				$this->error ( $msg );
				exit ();
			}
		}
	}
	
	public function write(){
		$pic = M('Facephoto')->select();
		foreach($pic as $key =>$value){
			M('Facephoto')->where('id='.$pic[$key]['id'])->setField(array('nid'=>'90000673'));
		}
	}
		
}
?>
