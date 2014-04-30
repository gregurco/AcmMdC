<?php

/**
 * This is the model class for table "{{processing}}".
 *
 * The followings are the available columns in table '{{processing}}':
 * @property integer $id
 * @property string $compiler
 * @property string $file_text
 * @property integer $result
 * @property string $tests
 * @property string $log_compile
 * @property integer $status
 * @property integer $p_id
 * @property double $limit_time
 * @property double $limit_memory
 */
class Processing extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{processing}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, compiler, file_text, p_id', 'required'),
			array('id, result, status, p_id', 'numerical', 'integerOnly'=>true),
			array('limit_time, limit_memory', 'numerical'),
			array('compiler', 'length', 'max'=>255),
			array('tests, log_compile', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, compiler, file_text, result, tests, log_compile, status, p_id, limit_time, limit_memory', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'compiler' => 'Compiler',
			'file_text' => 'File Text',
			'result' => 'Result',
			'tests' => 'Tests',
			'log_compile' => 'Log Compile',
			'status' => 'Status',
			'p_id' => 'P',
			'limit_time' => 'Limit Time',
			'limit_memory' => 'Limit Memory',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('compiler',$this->compiler,true);
		$criteria->compare('file_text',$this->file_text,true);
		$criteria->compare('result',$this->result);
		$criteria->compare('tests',$this->tests,true);
		$criteria->compare('log_compile',$this->log_compile,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('p_id',$this->p_id);
		$criteria->compare('limit_time',$this->limit_time);
		$criteria->compare('limit_memory',$this->limit_memory);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Processing the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
