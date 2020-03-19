<?php

/* @var $module string module name */
/* @var $tableName string table name */
/* @var $tableFields array table fields elements */
/* @var $tableComments array table fields comments */

?>
<template>
    <div class="components-container">
        <div class="filter-container">
    <?php foreach ($tableFields as $field) { ?>
        <el-input v-model="tableQuery.<?php echo $field; ?>" @keyup.enter.native="handleFilter" style="width: 200px;" placeholder="<?php echo (isset($tableComments[$field]) ? $tableComments[$field] : $field); ?>"></el-input>
    <?php } ?>
            <el-button style="margin-left: 10px;" @click="handleFilter" type="primary"><i class="el-icon-search"></i>
            </el-button>
            <el-button @click="handleReset" type="primary"><i class="el-icon-refresh"></i></el-button>
            <el-button @click="showDialog('create')" type="primary"><i class="el-icon-plus"></i>新增</el-button>
        </div>

        <el-table :data="tableData" v-loading.body="tableLoading" element-loading-text="拼命加载中" stripe border fit
                  highlight-current-row style="width: 100%">
            <?php foreach ($tableFields as $field) { ?>
                <el-table-column label="<?php echo (isset($tableComments[$field]) ? $tableComments[$field] : $field); ?>" prop="<?php echo $field; ?>" align="center"></el-table-column>
            <?php } ?>
            <el-table-column label="" align="center">
                <template slot-scope="scope">
                    <el-button size="small" @click="showDialog('view', scope.row)" type="text">修改</el-button>
                    <el-button size="small" @click="deleteItem(scope.row.<?php echo $tableName . '_id'; ?>)" type="text">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange"
            :current-page.sync="tableQuery.current_page"
            :page-sizes="[10, 20, 50]" :page-size="tableQuery.limit"
            layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="formTitle" :visible.sync="formVisible" width="55%" style="z-index:2001;">
            <el-form class="small-space" :model="createdItem" label-position="left" label-width="130px"
                     style='width: 90%;margin-left: 5%;'>

                <el-input type="hidden" v-model="createdItem.<?php echo $tableName; ?>_id">
                </el-input>
                <?php foreach ($tableFields as $field) { ?>
                    <el-form-item label="<?php echo (isset($tableComments[$field]) ? $tableComments[$field] : $field); ?>">
                        <el-input type="input" v-model="createdItem.<?php echo $field; ?>" placeholder="">
                        </el-input>
                    </el-form-item>
                <?php } ?>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="formVisible = false">取 消</el-button>
                <el-button type="primary" :loading="formSubmiting" @click="save">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {formatTime} from '@/utils';
    import {getQueryString} from '@/utils';

    export default {
    data() {
        return {
        tableQuery: {
            limit: 10,
                current_page: 1,
                name: null,
                status: null,
        },
        total: null,
            tableData: [],
            tableLoading: true,
            formTitle: '',
            formVisible: false,
            createdItem: {
                <?php echo $tableName; ?>_id: null,
            <?php foreach ($tableFields as $field) { ?>
                <?php echo $field; ?>: null,
            <?php } ?>
        },
        formSubmiting: false,
    };
    },
    mounted() {
        this.getList();
    },
    methods: {
        getList() {
            this.tableLoading = true;
            this.$api.<?php echo $module; ?>.<?php echo $tableName; ?>.index({
                data: this.tableQuery
            }).then(response => {
                this.total = response.data.data.total;
            this.tableData = response.data.data.list;
            this.tableLoading = false;
        }).catch(error => {
            this.tableLoading = false;
        console.log(error);
    });
    },
    handleFilter() {
        this.getList();
    },
    handleReset() {
        this.tableQuery.limit = 10;
        this.tableQuery.current_page = 1;
        <?php foreach ($tableFields as $field) { ?>
        this.tableQuery.<?php echo $field; ?> = null;
        <?php } ?>
        this.getList();
    },
    handleSizeChange(val) {
        this.tableQuery.limit = val;
        this.getList();
    },
    handleCurrentChange(val) {
        this.tableQuery.current_page = val;
        this.getList();
    },
    //保存
    save() {
        for (var ele in this.createdItem) {
            if (ele == "<?php echo $tableName; ?>_id") {
                continue;
            }
            if (this.createdItem[ele] == null) {
                this.formSubmiting = false;
                this.$notify({
                    title: '错误',
                    message: '设置项不可为空',
                    type: 'error',
                    duration: 3000,
                });
                return;
            }
        }
        this.formSubmiting = true;
        this.$api.<?php echo $module; ?>.<?php echo $tableName; ?>.save({
            data: this.createdItem
        }).then(response => {
            this.formSubmiting = false;
        this.formVisible = false;
        this.$notify({
            title: '成功',
            message: '保存记录成功',
            type: 'success',
            duration: 1500,
        });
        this.getList();
    }).catch(error => {
        console.log(error);
    this.formSubmiting = false;
    this.$notify({
        title: '错误',
        message: '保存记录失败',
        type: 'error',
        duration: 3000,
    });
    })
    },
    showDialog(type, row = null) {
        document.documentElement.style.overflow = 'hidden';
        this.formVisible = true;
        if (type == 'create') {
            this.formTitle = "新建";
            this.createdItem.<?php echo $tableName; ?>_id = null;
            <?php foreach ($tableFields as $field) { ?>
            this.createdItem.<?php echo $field; ?> = null;
            <?php } ?>
        } else if (type == 'view') {
                this.formTitle = "查看详情";
            this.createdItem.<?php echo $tableName; ?>_id = row.<?php echo $tableName; ?>_id;
            <?php foreach ($tableFields as $field) { ?>
            this.createdItem.<?php echo $field; ?> = row.<?php echo $field; ?>;
            <?php } ?>
        };
    },
    deleteItem(<?php echo $tableName . '_id'; ?>) {
        this.formSubmiting = true;
        this.$api.<?php echo $module; ?>.<?php echo $tableName; ?>.delete({
            data: {<?php echo $tableName . '_id'; ?>: <?php echo $tableName . '_id'; ?>}
        }).then(response => {
            this.formSubmiting = false;
        this.formVisible = false;
        this.$notify({
            title: '成功',
            message: '删除成功',
            type: 'success',
            duration: 1500,
        });
        this.getList();
    }).catch(error => {
        console.log(error);
    this.formSubmiting = false;
    this.$notify({
        title: '错误',
        message: '删除失败',
        type: 'error',
        duration: 3000,
    });
    })
    },
    }
    };
</script>
