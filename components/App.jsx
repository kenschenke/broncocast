import React from 'react';
import PropTypes from 'prop-types';
import { mapAppProps, mapAppDispatch } from './maps/App.map';
import { connect } from 'react-redux';
import MenuBar from './MenuBar.jsx';
import Profile from './Routes/Profile.jsx';
import MyBroadcasts from './Routes/MyBroadcasts.jsx';
import { AdminUsers } from './Admin/AdminUsers.jsx';
import { AdminGroups } from './Admin/AdminGroups.jsx';
import { AdminBroadcasts } from './Admin/AdminBroadcasts.jsx';
import { SystemOrgs } from './System/SystemOrgs.jsx';
import { SystemUsers } from './System/SystemUsers.jsx';
import About from './Routes/About.jsx';
import { Register } from './Profile/Register.jsx';
import { BrowserRouter, Route, Redirect } from 'react-router-dom';

class AppUi extends React.Component {
    render() {
        return (
            <div>
                <BrowserRouter>
                    <div>
                        <MenuBar/>
                        <div className="container-fluid mt-3">
                            <div className="row justify-content-center">
                                <div className="col-sm-12 col-md-12 col-lg-10">
                                    <div className="alert alert-secondary">
                                        <h3>BroncoCast</h3>
                                        <h4>{this.props.routeName}</h4>
                                    </div>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-lg-10 col-md-12 col-sm-12 offset-lg-1">
                                    <Route path="/profile" component={Profile}/>
                                    <Route path="/broadcasts" component={MyBroadcasts}/>
                                    <Route path="/admin/users" component={AdminUsers}/>
                                    <Route path="/admin/groups" component={AdminGroups}/>
                                    <Route path="/admin/broadcasts" component={AdminBroadcasts}/>
                                    <Route path="/system/orgs" component={SystemOrgs}/>
                                    <Route path="/system/users" component={SystemUsers}/>
                                    <Route path="/about" component={About}/>
                                    <Route path="/register" component={Register}/>
                                </div>
                            </div>
                        </div>
                        <Redirect from="/" to={'/' + window.InitialRoute}/>
                    </div>
                </BrowserRouter>
            </div>
        );
    }
}

AppUi.propTypes = {
    routeName: PropTypes.string.isRequired
};

export default connect(mapAppProps, mapAppDispatch)(AppUi);
