<?php
class EventController extends Controller
{
 private EventRepository $repo;
 public function __construct(){ $this->repo=new EventRepository(); }
 public function index():void{$user=Auth::user()??[];$branchId=strtolower((string)($user['role_name']??''))==='superadmin'?null:(int)($user['branch_id']??0);$this->view('admin/module-layout',['contentView'=>__DIR__.'/../Views/admin/events.php','adminEventsBranchId'=>$branchId]);}
 public function data():void{try{$u=Auth::user()??[];$role=strtolower((string)($u['role_name']??''));$branchId=$role==='superadmin'?null:(int)($u['branch_id']??0);$this->json(['success'=>true,'data'=>$this->repo->all($branchId>0?$branchId:null)]);}catch(Throwable $e){$this->json(['success'=>false,'message'=>'No fue posible cargar los eventos.'],500);}}
 public function status():void{try{$d=$this->getJsonInput();$id=(int)($d['id']??0);$status=(string)($d['status']??'');$u=Auth::user()??[];$role=strtolower((string)($u['role_name']??''));$branchId=$role==='superadmin'?null:(int)($u['branch_id']??0);if($id<=0||!in_array($status,['new','contacted','confirmed','cancelled'],true))$this->json(['success'=>false,'message'=>'Datos inválidos.'],422);$event=$this->repo->find($id);if(!$event||($branchId!==null&&(int)$event['branch_id']!==$branchId))$this->json(['success'=>false,'message'=>'No tienes acceso a este evento.'],403);$this->repo->updateStatus($id,$status);$this->json(['success'=>true,'message'=>'Estado actualizado.']);}catch(Throwable $e){$this->json(['success'=>false,'message'=>'No fue posible actualizar el estado.'],500);}}
 private function getJsonInput():array{$d=json_decode(file_get_contents('php://input'),true);return is_array($d)?$d:[];}
}
