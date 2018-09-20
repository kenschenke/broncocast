import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminGroupMembersProps, mapAdminGroupMembersDispatch } from '../maps/Admin/AdminGroupMembers.map';
import { connect } from 'react-redux';
import { DuxDialog } from 'duxpanel';

class AdminGroupMembersUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            selectedMemberId: 0,
            selectedNonMemberId: 0
        };
    }

    componentDidMount() {
        this.props.init(this.props.GroupId);
    }

    addMember = (e, UserId, UserName) => {
        e.preventDefault();
        this.props.addMember(this.props.GroupId, UserId, UserName);
    };

    removeMember = (e, MemberId, UserId, UserName) => {
        e.preventDefault();
        this.props.removeMember(MemberId, UserId, UserName);
    };

    render() {
        const members = this.props.members.map(member => {
            return (
                <div key={member.MemberId}
                     style={{padding:5, cursor:'pointer'}}
                     className={member.MemberId === this.state.selectedMemberId ? 'bg-secondary text-light' : ''}
                     onClick={() => this.setState({selectedMemberId:member.MemberId})}
                >
                    {member.UserName}
                    { member.MemberId === this.state.selectedMemberId &&
                    <div className="float-right">
                        <a href="#" className="badge badge-danger" onClick={e => this.removeMember(e, member.MemberId, member.UserId, member.UserName)}>Remove</a>
                    </div>
                    }
                </div>
            );
        });
        const nonMembers = this.props.nonMembers.map(user => {
            return (
                <div key={user.UserId}
                     style={{padding:5, cursor:'pointer'}}
                     className={user.UserId === this.state.selectedNonMemberId ? 'bg-secondary text-light' : ''}
                     onClick={() => this.setState({selectedNonMemberId:user.UserId})}
                >
                    {user.UserName}
                    { user.UserId === this.state.selectedNonMemberId &&
                        <div className="float-right">
                            <a href="#" className="badge badge-light" onClick={e => this.addMember(e, user.UserId, user.UserName)}>Add</a>
                        </div>
                    }
                </div>
            );
        });
        return (
            <DuxDialog name="admingroupmembers"
                       title="Group Members"
                       buttons={[{label:'Close',className:'btn',onClick:this.props.closeClicked}]}
                       show={true}
                       width={{
                           xs: '95%',
                           sm: '85%',
                           md: '75%',
                           lg: '65%',
                           xl: '50%'
                       }}
            >
                <div className="container">
                    <div className="row">
                        <div className="col-md-6 col-xs-12">
                            <h4>Members</h4>
                            <div style={{overflowY:'auto', width:'100%', height:'10em', border:'1px solid #ddd'}}>
                                {this.props.fetchingMembers &&
                                <h5 style={{textAlign:'center', fontStyle:'italic'}} className="mt-2 text-muted">Fetching</h5>
                                }
                                {!this.props.fetchingMembers && members}
                            </div>
                        </div>
                        <div className="col-md-6 col-xs-12">
                            <h4>Non Members</h4>
                            <div style={{overflowY:'auto', width:'100%', height:'10em', border:'1px solid #ddd'}}>
                                {this.props.fetchingNonMembers &&
                                <h5 style={{textAlign:'center', fontStyle:'italic'}} className="mt-2 text-muted">Fetching</h5>
                                }
                                {!this.props.fetchingNonMembers && nonMembers}
                            </div>
                            <input type="checkbox" onClick={e => this.props.setShowHidden(e.target.checked)}/> Show Hidden Users
                        </div>
                    </div>
                </div>
            </DuxDialog>
        );
    }
}

AdminGroupMembersUi.propTypes = {
    GroupId: PropTypes.number.isRequired,
    members: PropTypes.array.isRequired,
    nonMembers: PropTypes.array.isRequired,
    fetchingMembers: PropTypes.bool.isRequired,
    fetchingNonMembers: PropTypes.bool.isRequired,

    addMember: PropTypes.func.isRequired,
    closeClicked: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired,
    removeMember: PropTypes.func.isRequired,
    setShowHidden: PropTypes.func.isRequired,
};

export const AdminGroupMembers = connect(mapAdminGroupMembersProps, mapAdminGroupMembersDispatch)(AdminGroupMembersUi);
