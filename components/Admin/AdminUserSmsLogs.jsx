import React from 'react';
import PropTypes from 'prop-types';
import { DuxOkDialog } from 'duxpanel';
import { DuxTable } from 'duxtable';

export const AdminUserSmsLogs = props => {
    const columns = [
        {
            title: 'Time',
            field: 'Time',
            sortable: false
        },
        {
            title: 'Message',
            field: 'Message',
            sortable: false
        }
    ];

    return (
        <DuxOkDialog show={props.show}
                     title="User Delivery Problems"
                     okClassName="btn btn-primary"
                     onCancel={props.closeClicked}
                     onOk={props.closeClicked}
                     width={{
                         sm: '95%',
                         lg: '50%'
                     }}
        >
            <DuxTable name="smslogs"
                      columns={columns}
                      rowKey="Time"
                      data={props.smsLogs}
                      pagination={false}
                      bodyHeight={200}
                      showSearch={false}
            />
        </DuxOkDialog>
    );
};

AdminUserSmsLogs.propTypes = {
    show: PropTypes.bool.isRequired,
    smsLogs: PropTypes.array.isRequired,

    closeClicked: PropTypes.func.isRequired
};
