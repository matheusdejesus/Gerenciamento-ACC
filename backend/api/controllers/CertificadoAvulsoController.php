<?php
namespace Controllers;
use Models\CertificadoAvulso;

class CertificadoAvulsoController extends Controller
{
    public function enviar()
    {
        try {
            $user = $this->getUser();
            if ($user->tipo !== 'aluno') {
                http_response_code(403);
                echo json_encode(['success'=>false,'error'=>'Acesso negado']);
                return;
            }

            // valida POST
            $req = $_POST;
            if (empty($req['titulo_avulso']) || empty($req['horas_avulso']) || empty($req['coordenador_id'])) {
                http_response_code(400);
                echo json_encode(['success'=>false,'error'=>'Dados incompletos']);
                return;
            }
            
            $titulo     = trim($req['titulo_avulso']);
            $horas      = (int)$req['horas_avulso'];
            $coordId    = (int)$req['coordenador_id'];
            $obs        = trim($req['observacao_avulso'] ?? '');

            if ($horas <= 0) {
                http_response_code(400);
                echo json_encode(['success'=>false,'error'=>'Carga horária inválida']);
                return;
            }

            // valida arquivo
            if (!isset($_FILES['arquivo_comprovante']) || $_FILES['arquivo_comprovante']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success'=>false,'error'=>'Falha no upload do arquivo']);
                return;
            }

            // move para uploads/certificadoavulso
            $uploadDir = __DIR__ . '/../../uploads/certificadoavulso/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time().'_'.basename($_FILES['arquivo_comprovante']['name']);
            $destPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['arquivo_comprovante']['tmp_name'], $destPath)) {
                http_response_code(500);
                echo json_encode(['success'=>false,'error'=>'Não foi possível salvar o arquivo']);
                return;
            }

            // grava no banco
            $id = CertificadoAvulso::create([
                'aluno_id'        => $user->id,
                'coordenador_id'  => $coordId,
                'titulo'          => $titulo,
                'observacao'      => $obs,
                'horas'           => $horas,
                'caminho_arquivo' => 'uploads/certificadoavulso/'.$fileName
            ]);

            if ($id) {
                echo json_encode(['success'=>true,'message'=>'Certificado avulso enviado com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['success'=>false,'error'=>'Erro ao gravar no banco de dados']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>'Erro interno: '.$e->getMessage()]);
        }
    }
}