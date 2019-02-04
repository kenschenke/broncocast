import React from 'react';
import PropTypes from 'prop-types';
import { mapMenuBarProps, mapMenuBarDispatch } from './maps/MenuBar.map';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faCheck from '@fortawesome/fontawesome-free-solid/faCheck';

class MenuBarUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    adminOrgClicked = (e, OrgId, OrgName) => {
        e.preventDefault();
        this.props.adminOrgClicked(OrgId, OrgName);
    };

    render() {
        const AdminOrgs = window.AdminOrgs.map(org => {
            return (
                <a key={org.OrgId} className="dropdown-item" href="#" onClick={e => this.adminOrgClicked(e, org.OrgId, org.OrgName)}>
                    <div style={{width:'1.5em', display:'inline-block'}}>
                        { org.OrgId === this.props.adminOrg &&
                        <FontAwesomeIcon icon={faCheck}/>
                        }
                    </div>
                    {org.OrgName}
                </a>
            );
        });

        return (
            <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
                <button className="navbar-toggler" type="button" data-toggle="collapse" data-target="#menubar" aria-controls="menubar" aria-expanded="false" aria-label="Toggle menu">
                    <span className="navbar-toggler-icon"></span>
                </button>
                <div id="menubar" className="collapse navbar-collapse">
                    <ul className="navbar-nav">
                        <Link className={'nav-link' + (this.props.route==='profile' ? ' active' : '')} to="/profile">Profile</Link>
                        <Link className={'nav-link' + (this.props.route==='broadcasts' ? ' active' : '')} to="/broadcasts">My Broadcasts</Link>
                        { window.AdminOrgs.length &&
                            <div className="nav-item dropdown">
                                <a className="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup={true} aria-expanded={false}>Admin</a>
                                <div className="dropdown-menu">
                                    <Link className="dropdown-item" to="/admin/users">Users</Link>
                                    <Link className="dropdown-item" to="/admin/groups">Groups</Link>
                                    <Link className="dropdown-item" to="/admin/broadcasts">Broadcasts</Link>
                                    { window.AdminOrgs.length > 1 &&
                                    <div className="dropdown-divider"></div>
                                    }
                                    {window.AdminOrgs.length > 1 && AdminOrgs}
                                </div>
                            </div>
                        }
                        { window.IsSystemAdmin &&
                        <div className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup={true} aria-expanded={false}>System</a>
                            <div className="dropdown-menu">
                                <Link className="dropdown-item" to="/system/orgs">Organizations</Link>
                                <Link className="dropdown-item" to="/system/users">Users</Link>
                            </div>
                        </div>
                        }
                        <Link className={'nav-link' + (this.props.route==='about' ? ' active' : '')} to="/about">About</Link>
                        <li className="nav-item">
                            <a className="nav-link" href="/logout">Log Out</a>
                        </li>
                    </ul>
                </div>
            </nav>
        );
    }
}

MenuBarUi.propTypes = {
    route: PropTypes.string.isRequired,
    adminOrg: PropTypes.number.isRequired,
    init: PropTypes.func.isRequired,

    adminOrgClicked: PropTypes.func.isRequired
};

const Container = connect(mapMenuBarProps, mapMenuBarDispatch)(MenuBarUi);

export default Container;
