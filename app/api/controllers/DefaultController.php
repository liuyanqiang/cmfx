<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuyanqiang
 * Date: 2018/6/7
 * Time: 下午12:04
 */
namespace UI\Controllers;

class DefaultController extends BaseController
{

    public function indexAction()
    {
        return $this->success([
            'codeinfo' => $this->code_dict,
            'exclude' => $this->exclude,
            'version' => $this->version
        ], "接口说明");
    }
}
