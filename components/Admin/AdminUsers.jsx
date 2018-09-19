import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminUsersProps, mapAdminUsersDispatch } from '../maps/Admin/AdminUsers.map';
import { connect } from 'react-redux';
import { DuxTable } from 'duxtable';
import { formatPhoneNumber } from '../../util/util';
import { InputDialog } from '../InputDialog.jsx';
import { DuxYesNoDialog } from 'duxpanel';

class AdminUsersUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            nameInitialValue: '',  // initial value for name change dialog
            adminOrgId: 0
        };
    }

    componentDidMount() {
        this.props.init();
        this.props.adminOrgChanged();
    }

    componentDidUpdate() {
        if (this.props.adminOrgId !== this.state.adminOrgId) {
            this.props.adminOrgChanged();
            this.setState({adminOrgId: this.props.adminOrgId});
        }
    }

    changeNameClicked = (MemberId, Name) => {
        this.setState({nameInitialValue: Name});

        this.props.changeNameClicked(MemberId);
    };

    getStatusText = user => {
        if (user.Hidden) {
            return 'Hidden';
        } else if (user.Approved) {
            return 'Approved';
        } else {
            return 'Not Approved';
        }
    };

    renderStatus = user => {
        const StatusText = this.getStatusText(user);
        return StatusText === 'Not Approved' ? <span className="text-danger">{StatusText}</span> : StatusText;
    };

    selectedRender = (item, colNum) => {
        if (colNum !== -1) {
            return undefined;
        }

        const contacts = item.Contacts.map(contact => {
            let display = contact.Contact;
            if (contact.CarrierId) {
                display = formatPhoneNumber(display);
                const carriers = window.Carriers.filter(carrier => carrier.CarrierId === contact.CarrierId);
                if (carriers.length === 1) {
                    display += ` (${carriers[0].CarrierName})`;
                }
            }
            return <div key={contact.Contact} className="ml-3">{display}</div>
        });

        return (
            <div>
                <span className="text-warning">Contact Information</span><br/>
                {contacts}
                <div className="mt-2">
                    { !item.Approved &&
                    <button type="button" className="btn btn-sm ml-2 btn-success" onClick={() => this.props.approveUser(item.MemberId)}>Approve</button>
                    }
                    <button type="button" className="btn btn-sm ml-2" onClick={() => this.changeNameClicked(item.MemberId,item.UsrName)}>Name</button>
                    <button type="button" className="btn btn-sm ml-2" onClick={() => this.props.hideUnhideUser(item.MemberId)}>{item.Hidden ? 'Unhide' : 'Hide'}</button>
                    <button type="button" className="btn btn-sm btn-danger ml-2" onClick={() => this.props.removeUser(item.MemberId)}>Remove</button>
                </div>
            </div>
        );
    };

    statusSortCallback = (item1, item2, sortAscending) => {
        const status1 = this.getStatusText(item1);
        const status2 = this.getStatusText(item2);

        if (status1 > status2) {
            return sortAscending ? 1 : -1;
        } else if (status1 < status2) {
            return sortAscending ? -1 : 1;
        } else {
            return 0;
        }
    };

    render() {
        const columns = [
            {
                field: 'UsrName',
                title: 'Name'
            },
            {
                field: 'Groups',
                title: 'Groups',
                hidden: {xs: true, sm: true, md: false}
            },
            {
                field: 'Status',
                title: 'Status',
                render: this.renderStatus,
                hidden: {xs: true, sm: false},
                sortCallback: this.statusSortCallback
            }
        ];

        return (
            <div>
                <DuxTable name="users"
                          columns={columns}
                          striped={true}
                          data={this.props.users}
                          rowKey="MemberId"
                          sortColumn={0}
                          fetchingData={this.props.fetching}
                          fetchingMsg="Retrieving Users"
                          emptyMsg="No Users Found"
                          selectionMode="single"
                          selectedRenderCallback={this.selectedRender}
                />
                <input type="checkbox" onChange={e => this.props.setHidden(e.target.checked)}/> Show Hidden Users<br/>

                <InputDialog show={this.props.showChangeNameDialog}
                             title="Change User Name"
                             label="User Name"
                             placeholder="Enter user name"
                             onValidate={name => name.length < 1 ? 'User name required' : undefined}
                             onOk={this.props.changeNameOk}
                             onCancel={this.props.changeNameCancel}
                             initialValue={this.state.nameInitialValue}
                />

                <DuxYesNoDialog show={this.props.showRemoveDialog}
                                title="Confirm Remove User"
                                noClassName="btn"
                                yesClassName="btn btn-danger"
                                onNo={this.props.removeDialogNo}
                                onYes={this.props.removeDialogYes}
                >
                    Are you sure you want to remove this user?
                </DuxYesNoDialog>
            </div>
        );
    }
}

AdminUsersUi.propTypes = {
    adminOrgId: PropTypes.number.isRequired,
    fetching: PropTypes.bool.isRequired,
    users: PropTypes.array.isRequired,
    showChangeNameDialog: PropTypes.bool.isRequired,
    showRemoveDialog: PropTypes.bool.isRequired,

    adminOrgChanged: PropTypes.func.isRequired,
    approveUser: PropTypes.func.isRequired,
    hideUnhideUser: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired,
    removeUser: PropTypes.func.isRequired,
    setHidden: PropTypes.func.isRequired,
    changeNameClicked: PropTypes.func.isRequired,
    changeNameOk: PropTypes.func.isRequired,
    changeNameCancel: PropTypes.func.isRequired,
    removeDialogNo: PropTypes.func.isRequired,
    removeDialogYes: PropTypes.func.isRequired,
};

export const AdminUsers = connect(mapAdminUsersProps, mapAdminUsersDispatch)(AdminUsersUi);
