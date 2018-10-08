import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminUsersProps, mapAdminUsersDispatch } from '../maps/Admin/AdminUsers.map';
import { connect } from 'react-redux';
import { DuxTable } from 'duxtable';
import { formatPhoneNumber } from '../../util/util';
import { InputDialog } from '../InputDialog.jsx';
import { DuxYesNoDialog } from 'duxpanel';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faAngleDownDown from '@fortawesome/fontawesome-free-solid/faAngleDoubleDown';
import faAngleDownUp from '@fortawesome/fontawesome-free-solid/faAngleDoubleUp';
import { AdminUserSmsLogs } from './AdminUserSmsLogs.jsx';

class AdminUsersUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            nameInitialValue: '',  // initial value for name change dialog
            adminOrgId: 0,
            filterShowMore: false,
            showSmsLogs: false,
            smsLogs: []
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

    filterRadioChanged = filter => {
        this.props.setFilterOn(filter);
    };

    getStatusText = user => {
        if (user.Hidden) {
            return 'Hidden';
        } else if (user.Approved) {
            const Status = 'Approved' + (user.IsAdmin ? ' (Admin)' : '');
            return user.HasDeliveryError ?
                <span>{Status} <span className="badge badge-danger">Delivery Problems</span></span> : Status;
        } else {
            return <span className="badge badge-warning">Not Approved</span>
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
            if (display.length === 10 && display.indexOf(/[^0-9]/) === -1) {
                if (item.SmsLogs.filter(smsLog => {
                    return contact.ContactId === smsLog.ContactId;
                }).length) {
                    display = <div>
                        {formatPhoneNumber(display)}
                        <a className="ml-2 badge badge-danger" onClick={e => {e.preventDefault(); this.showSmsLogs(item.SmsLogs);}}>Show Delivery Problems</a>
                    </div>
                } else {
                    display = formatPhoneNumber(display);
                }
            }
            return <div key={contact.Contact} className="ml-3">{display}</div>
        });

        return (
            <div>
                <span className="text-warning">Contact Information</span><br/>
                {contacts}
                <div className="mt-2">
                    {!item.Approved &&
                    <button type="button" className="btn btn-sm ml-2 btn-success"
                            onClick={() => this.props.approveUser(item.MemberId)}>Approve</button>
                    }
                    <button type="button" className="btn btn-sm ml-2"
                            onClick={() => this.changeNameClicked(item.MemberId, item.UsrName)}>Name
                    </button>
                    <button type="button" className="btn btn-sm ml-2"
                            onClick={() => this.props.hideUnhideUser(item.MemberId)}>{item.Hidden ? 'Unhide' : 'Hide'}</button>
                    <button type="button" className="btn btn-sm ml-2"
                            onClick={() => this.props.setDropAdmin(item.MemberId)}>{item.IsAdmin ? 'Drop Admin' : 'Add Admin'}</button>
                    <button type="button" className="btn btn-sm btn-danger ml-2"
                            onClick={() => this.props.removeUser(item.MemberId)}>Remove
                    </button>
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

    showSmsLogs = smsLogs => {
        this.setState({
            showSmsLogs: true,
            smsLogs: smsLogs
        });
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
                <div className="container-fluid mb-2 alert alert-dark d-none d-lg-block d-xl-block">
                    <div className="row">
                        <div className="col mt-1">
                            <span className="badge badge-secondary mr-2"
                                  style={{fontSize: '1.1em'}}>{this.props.hiddenUsers}</span>
                            <span className={this.props.unapprovedUsersClass}
                                  style={{fontSize: '1.1em'}}>{this.props.unapprovedUsers}</span>
                            <span className={this.props.deliveryProblemsClass}
                                  style={{fontSize: '1.1em'}}>{this.props.deliveryProblems}</span>
                        </div>
                        <div className="col" style={{textAlign: 'right'}}>
                            {!this.state.filterShowMore &&
                            <button type="button" className="btn btn-primary"
                                    onClick={() => this.setState({filterShowMore: true})}>
                                Filter <FontAwesomeIcon icon={faAngleDownDown}/>
                            </button>
                            }
                            {this.state.filterShowMore &&
                            <button type="button" className="btn btn-primary"
                                    onClick={() => this.setState({filterShowMore: false})}>
                                Show Less <FontAwesomeIcon icon={faAngleDownUp}/>
                            </button>
                            }
                        </div>
                    </div>
                    {this.state.filterShowMore &&
                    <div className="row">
                        <div className="col">
                            <div className="form-check">
                                <input type="radio"
                                       name="filter"
                                       className="form-check-input"
                                       defaultChecked={this.props.filterOn === 'hidehidden'}
                                       onChange={e => this.filterRadioChanged('hidehidden')}
                                />
                                <label className="form-check-label">Hide hidden users</label>
                            </div>
                            <div className="form-check">
                                <input type="radio"
                                       name="filter"
                                       className="form-check-input"
                                       defaultChecked={this.props.filterOn === 'showhidden'}
                                       onChange={e => this.filterRadioChanged('showhidden')}
                                />
                                <label className="form-check-label">Show hidden users</label>
                            </div>
                            <div className="form-check">
                                <input type="radio"
                                       name="filter"
                                       className="form-check-input"
                                       defaultChecked={this.props.filterOn === 'onlyunapproved'}
                                       onChange={e => this.filterRadioChanged('onlyunapproved')}
                                />
                                <label className="form-check-label">Show only unapproved users</label>
                            </div>
                            <div className="form-check">
                                <input type="radio"
                                       name="filter"
                                       className="form-check-input"
                                       defaultChecked={this.props.filterOn === 'onlydeliveryproblems'}
                                       onChange={e => this.filterRadioChanged('onlydeliveryproblems')}
                                />
                                <label className="form-check-label">Show only users with delivery problems</label>
                            </div>
                        </div>
                    </div>
                    }
                </div>
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

                <AdminUserSmsLogs show={this.state.showSmsLogs}
                                  smsLogs={this.state.smsLogs}
                                  closeClicked={() => this.setState({showSmsLogs:false})}
                />
            </div>
        );
    }
}

AdminUsersUi.propTypes = {
    adminOrgId: PropTypes.number.isRequired,
    filterOn: PropTypes.string.isRequired,
    fetching: PropTypes.bool.isRequired,
    users: PropTypes.array.isRequired,
    showChangeNameDialog: PropTypes.bool.isRequired,
    showRemoveDialog: PropTypes.bool.isRequired,
    hiddenUsers: PropTypes.string.isRequired,
    unapprovedUsers: PropTypes.string.isRequired,
    unapprovedUsersClass: PropTypes.string.isRequired,
    deliveryProblems: PropTypes.string.isRequired,
    deliveryProblemsClass: PropTypes.string.isRequired,

    adminOrgChanged: PropTypes.func.isRequired,
    approveUser: PropTypes.func.isRequired,
    hideUnhideUser: PropTypes.func.isRequired,
    setDropAdmin: PropTypes.func.isRequired,
    setFilterOn: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired,
    removeUser: PropTypes.func.isRequired,
    changeNameClicked: PropTypes.func.isRequired,
    changeNameOk: PropTypes.func.isRequired,
    changeNameCancel: PropTypes.func.isRequired,
    removeDialogNo: PropTypes.func.isRequired,
    removeDialogYes: PropTypes.func.isRequired,
};

export const AdminUsers = connect(mapAdminUsersProps, mapAdminUsersDispatch)(AdminUsersUi);
