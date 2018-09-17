import React from 'react';
import PropTypes from 'prop-types';
import { mapMenuBarProps, mapMenuBarDispatch } from './maps/MenuBar.map';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';

class MenuBarUi extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
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
                        <Link className={'nav-link' + (this.props.route==='admin' ? ' active' : '')} to="/admin">Admin</Link>
                        }
                        { window.IsSystemAdmin &&
                        <Link className={'nav-link' + (this.props.route==='system' ? ' active' : '')} to="/system">System</Link>
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
    route: PropTypes.string.isRequired
};

const Container = connect(mapMenuBarProps, mapMenuBarDispatch)(MenuBarUi);

export default Container;
