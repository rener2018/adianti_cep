<?php
/**
 * PessoaForm Form
 * @author  <your name here>
 */
class PessoaForm extends TPage
{
    protected $form; // form
    private $formFields = [];
    private static $database = 'mini_erp';
    private static $activeRecord = 'Pessoa';
    private static $primaryKey = 'id';
    private static $formName = 'list_Pessoa';

    use Adianti\Base\AdiantiMasterDetailTrait;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle('Cadastro de pessoa');

        $id = new TEntry('id');
        $system_user_id = new TDBCombo('system_user_id', 'permission', 'SystemUsers', 'id', '{name}','name asc'  );
        $dt_ativacao = new TDate('dt_ativacao');
        $dt_desativacao = new TDate('dt_desativacao');
        $grupo_id = new TDBCheckGroup('grupo_id', 'mini_erp', 'Grupo', 'id', '{nome}','nome asc'  );
        $nome = new TEntry('nome');
        $documento = new TEntry('documento');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $obs = new TText('obs');
        $cidade_estado_id = new TDBCombo('cidade_estado_id', 'mini_erp', 'Estado', 'id', '{nome}','nome asc'  );
        $cidade_id = new TCombo('cidade_id');
        $bairro = new TEntry('bairro');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        $cep = new TEntry('cep');
        $contato_pessoa_email = new TEntry('contato_pessoa_email');
        $contato_pessoa_nome = new TEntry('contato_pessoa_nome');
        $contato_pessoa_telefone = new TEntry('contato_pessoa_telefone');
        $contato_pessoa_obs = new TEntry('contato_pessoa_obs');
        $contato_pessoa_id = new THidden('contato_pessoa_id');

        $cidade_estado_id->setChangeAction(new TAction([$this,'onChangecidade_estado_id']));

        $dt_ativacao->addValidation('Data de ativação', new TRequiredValidator()); 
        $nome->addValidation('Nome', new TRequiredValidator()); 
        $documento->addValidation('Documento', new TRequiredValidator()); 
        $cidade_id->addValidation('Cidade', new TRequiredValidator()); 

        $id->setEditable(false);
        $system_user_id->enableSearch();
        $dt_ativacao->setValue(date('d/m/Y'));
        $grupo_id->setLayout('horizontal');
        $dt_ativacao->setDatabaseMask('yyyy-mm-dd');
        $dt_desativacao->setDatabaseMask('yyyy-mm-dd');

        $cep->setMask('99999-999');
        $fone->setMask('(99)99999-9999');
        $dt_ativacao->setMask('dd/mm/yyyy');
        $dt_desativacao->setMask('dd/mm/yyyy');
        $contato_pessoa_telefone->setMask('(99)99999-9999');

        $id->setSize(150);
        $cep->setSize('100%');
        $rua->setSize('100%');
        $nome->setSize('100%');
        $fone->setSize('100%');
        $grupo_id->setSize(180);
        $email->setSize('100%');
        $bairro->setSize('100%');
        $numero->setSize('100%');
        $dt_ativacao->setSize(150);
        $obs->setSize('100%', 100);
        $documento->setSize('100%');
        $cidade_id->setSize('100%');
        $dt_desativacao->setSize(150);
        $complemento->setSize('100%');
        $system_user_id->setSize('100%');
        $cidade_estado_id->setSize('100%');
        $contato_pessoa_obs->setSize('100%');
        $contato_pessoa_nome->setSize('100%');
        $contato_pessoa_email->setSize('100%');
        $contato_pessoa_telefone->setSize('100%');



        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Usuário do sistema:')],[$system_user_id]);
        $this->form->addFields([new TLabel('Data de ativação:', '#ff0000')],[$dt_ativacao],[new TLabel('Data de desativação:')],[$dt_desativacao]);
        $this->form->addFields([new TLabel('Grupo:')],[$grupo_id]);
        $this->form->addContent([new TFormSeparator('Dados pessoais', '#333333', '18', '#eeeeee')]);
        $this->form->addFields([new TLabel('Nome:', '#ff0000')],[$nome],[new TLabel('Documento:', '#ff0000')],[$documento]);
        $this->form->addFields([new TLabel('Telefone:')],[$fone],[new TLabel('Email:')],[$email]);
        $this->form->addFields([new TLabel('Observação:')],[$obs]);
        $this->form->addContent([new TFormSeparator('Localização', '#333333', '18', '#eeeeee')]);
        $this->form->addFields([new TLabel('Estado:', '#ff0000')],[$cidade_estado_id],[new TLabel('Cidade:', '#ff0000')],[$cidade_id]);
        $this->form->addFields([new TLabel('Bairro:')],[$bairro],[new TLabel('Rua:')],[$rua]);
        $this->form->addFields([new TLabel('Número:')],[$numero],[new TLabel('Complemento:')],[$complemento]);
        $this->form->addFields([new TLabel('CEP:')],[$cep],[],[]);
        $this->form->addContent([new TFormSeparator('Contato', '#333333', '18', '#eeeeee')]);
        $this->form->addFields([new TLabel('Email:', '#ff0000')],[$contato_pessoa_email],[new TLabel('Nome:', '#ff0000')],[$contato_pessoa_nome]);
        $this->form->addFields([new TLabel('Tefone:')],[$contato_pessoa_telefone],[new TLabel('Observação:')],[$contato_pessoa_obs]);
        $this->form->addFields([$contato_pessoa_id]);         
        $add_contato_pessoa = new TButton('add_contato_pessoa');

        $action_contato_pessoa = new TAction(array($this, 'onAddContatoPessoa'));

        $add_contato_pessoa->setAction($action_contato_pessoa, 'Adicionar');
        $add_contato_pessoa->setImage('fa:plus #000000');

        $this->form->addFields([$add_contato_pessoa]);

        $this->contato_pessoa_list = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->contato_pessoa_list->style = 'width:100%';
        $this->contato_pessoa_list->class .= ' table-bordered';
        $this->contato_pessoa_list->disableDefaultClick();
        $this->contato_pessoa_list->addQuickColumn('', 'edit', 'left', 50);
        $this->contato_pessoa_list->addQuickColumn('', 'delete', 'left', 50);

        $column_contato_pessoa_email = $this->contato_pessoa_list->addQuickColumn('Email', 'contato_pessoa_email', 'left');
        $column_contato_pessoa_nome = $this->contato_pessoa_list->addQuickColumn('Nome', 'contato_pessoa_nome', 'left');
        $column_contato_pessoa_telefone = $this->contato_pessoa_list->addQuickColumn('Telefone:', 'contato_pessoa_telefone', 'left');
        $column_contato_pessoa_obs = $this->contato_pessoa_list->addQuickColumn('Observação', 'contato_pessoa_obs', 'left');

        $this->contato_pessoa_list->createModel();
        $this->form->addContent([$this->contato_pessoa_list]);

        // create the form actions
        $btn_onsave = $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction('Novo', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);

    }

    public static function onChangecidade_estado_id($param)
    {
        try
        {
            if ($param['cidade_estado_id'])
            { 
                $criteria = TCriteria::create(['estado_id' => (int) $param['cidade_estado_id']]); 
                TDBCombo::reloadFromModel(self::$formName, 'cidade_id', 'mini_erp', 'Cidade', 'id', '{nome}', 'nome asc', $criteria, TRUE); 
            } 
            else 
            { 
                TCombo::clearField(self::$formName, 'cidade_id'); 
            }  

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    } 

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction

            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/

            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Pessoa(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $this->fireEvents($object);

            $repository = PessoaGrupo::where('pessoa_id', '=', $object->id);
            $repository->delete(); 

            if ($data->grupo_id) 
            {
                foreach ($data->grupo_id as $grupo_id) 
                {
                    $pessoa_grupo = new PessoaGrupo;

                    $pessoa_grupo->grupo_id = $grupo_id;
                    $pessoa_grupo->pessoa_id = $object->id;
                    $pessoa_grupo->store();
                }
            }

            $messageAction = new TAction(['PessoaList', 'onShow']);   

            $contato_pessoa_items = $this->storeItems('Contato', 'pessoa_id', $object, 'contato_pessoa', function($masterObject, $detailObject){ 

                //code here

            }); 

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/

            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $messageAction);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear();

        TSession::setValue('contato_pessoa_items', null);

        $this->onReload();
    }  

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Pessoa($key); // instantiates the Active Record 

                $object->cidade_estado_id = $object->cidade->estado_id;

                $criteria = TCriteria::create(['pessoa_id'=>$object->id]);
                $object->grupo_id = PessoaGrupo::getIndexedArray('grupo_id', 'grupo_id', $criteria);

                $contato_pessoa_items = $this->loadItems('Contato', 'pessoa_id', $object, 'contato_pessoa', function($masterObject, $detailObject, $objectItems){ 

                    //code here

                }); 

                $this->form->setData($object); // fill the form 

                $this->fireEvents($object);
                $this->onReload();

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onAddContatoPessoa( $param )
    {
        try
        {
            $data = $this->form->getData();

            if(!$data->contato_pessoa_email)
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Email'));
            }             
            if(!$data->contato_pessoa_nome)
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Nome'));
            }             

            $contato_pessoa_items = TSession::getValue('contato_pessoa_items');
            $key = isset($data->contato_pessoa_id) && $data->contato_pessoa_id ? $data->contato_pessoa_id : uniqid();
            $fields = []; 

            $fields['contato_pessoa_email'] = $data->contato_pessoa_email;
            $fields['contato_pessoa_nome'] = $data->contato_pessoa_nome;
            $fields['contato_pessoa_telefone'] = $data->contato_pessoa_telefone;
            $fields['contato_pessoa_obs'] = $data->contato_pessoa_obs;
            $contato_pessoa_items[ $key ] = $fields;

            TSession::setValue('contato_pessoa_items', $contato_pessoa_items);

            // clear product form fields after add
            $data->contato_pessoa_email = '';
            $data->contato_pessoa_nome = '';
            $data->contato_pessoa_telefone = '';
            $data->contato_pessoa_obs = '';

            $data->contato_pessoa_id = '';

            $this->form->setData($data);
            $this->fireEvents($data);
            $this->onReload( $param );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            $this->fireEvents($data);
            new TMessage('error', $e->getMessage());
        }
    }

    public function onEditContatoPessoa( $param )
    {
        $data = $this->form->getData();

        // read session items
        $items = TSession::getValue('contato_pessoa_items');

        // get the session item
        $item = $items[$param['contato_pessoa_id_row_id']];

        $data->contato_pessoa_email = $item['contato_pessoa_email'];
        $data->contato_pessoa_nome = $item['contato_pessoa_nome'];
        $data->contato_pessoa_telefone = $item['contato_pessoa_telefone'];
        $data->contato_pessoa_obs = $item['contato_pessoa_obs'];

        $data->contato_pessoa_id = $param['contato_pessoa_id_row_id'];

        // fill product fields
        $this->form->setData( $data );

        $this->fireEvents($data);

        $this->onReload( $param );
    }

    public function onDeleteContatoPessoa( $param )
    {
        $data = $this->form->getData();

        $data->contato_pessoa_email = '';
        $data->contato_pessoa_nome = '';
        $data->contato_pessoa_telefone = '';
        $data->contato_pessoa_obs = '';

        // clear form data
        $this->form->setData( $data );

        // read session items
        $items = TSession::getValue('contato_pessoa_items');

        // delete the item from session
        unset($items[$param['contato_pessoa_id_row_id']]);
        TSession::setValue('contato_pessoa_items', $items);

        $this->fireEvents($data);

        // reload sale items
        $this->onReload( $param );
    }

    public function onReloadContatoPessoa( $param )
    {
        $items = TSession::getValue('contato_pessoa_items'); 

        $this->contato_pessoa_list->clear(); 

        if($items) 
        { 
            $cont = 1; 
            foreach ($items as $key => $item) 
            {
                $rowItem = new StdClass;

                $action_del = new TAction(array($this, 'onDeleteContatoPessoa')); 
                $action_del->setParameter('contato_pessoa_id_row_id', $key);   

                $action_edi = new TAction(array($this, 'onEditContatoPessoa'));  
                $action_edi->setParameter('contato_pessoa_id_row_id', $key);  

                $button_del = new TButton('delete_contato_pessoa'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction($action_del, '');
                $button_del->setImage('fa:trash-o'); 
                $button_del->setFormName($this->form->getName());

                $button_edi = new TButton('edit_contato_pessoa'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction($action_edi, '');
                $button_edi->setImage('bs:edit');
                $button_edi->setFormName($this->form->getName());

                $rowItem->edit = $button_edi;
                $rowItem->delete = $button_del;

                $rowItem->contato_pessoa_email = isset($item['contato_pessoa_email']) ? $item['contato_pessoa_email'] : '';
                $rowItem->contato_pessoa_nome = isset($item['contato_pessoa_nome']) ? $item['contato_pessoa_nome'] : '';
                $rowItem->contato_pessoa_telefone = isset($item['contato_pessoa_telefone']) ? $item['contato_pessoa_telefone'] : '';
                $rowItem->contato_pessoa_obs = isset($item['contato_pessoa_obs']) ? $item['contato_pessoa_obs'] : '';

                $row = $this->contato_pessoa_list->addItem($rowItem);

                $cont++;
            } 
        } 
    } 

    public function onShow()
    {
        TSession::setValue('contato_pessoa_items', null);

        $this->onReload();
    } 

    public function fireEvents( $object )
    {
        $obj = new stdClass;
        if(get_class($object) == 'stdClass')
        {
            if(isset($object->cidade_estado_id))
            {
                $obj->cidade_estado_id = $object->cidade_estado_id;
            }
            if(isset($object->cidade_id))
            {
                $obj->cidade_id = $object->cidade_id;
            }
        }
        else
        {
            if(isset($object->cidade->estado_id))
            {
                $obj->cidade_estado_id = $object->cidade->estado_id;
            }
            if(isset($object->cidade_id))
            {
                $obj->cidade_id = $object->cidade_id;
            }
        }
        TForm::sendData(self::$formName, $obj);
    }  

    public function onReload($params = null)
    {
        $this->loaded = TRUE;

        $this->onReloadContatoPessoa($params);
    }

    public function show() 
    { 
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') ) 
        { 
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }

}

