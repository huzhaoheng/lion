<template>
	<div>

		<div class="row" style="margin-left: 10px; margin-top: -20px">
			<h1> {{ query.name }}  </h1>
		</div>

		<div class="row">
			<div class="col-md-4" style="margin-left: 10px; margin-bottom: 10px">
				<button class="btn btn-warning" v-on:click="editQuery"> Edit Query </button>
				<button class="btn btn-danger" v-on:click="deleteQuery"> Delete Query </button>
				<button class="btn btn-success float-right" v-on:click="submitQuery"> Submit Query </button>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3" style="height:80vh; overflow-y: scroll; margin-left: 10px">
				<table class="table table-striped table-dark">
					<thead>
						<tr>
							<th scope="col"> Time </th>
							<th scope="col"> Runtime (ms) </th>
							<th scope="col"> Data </th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(historyItem, index) in history">
							<td>
								{{ historyItem.created_at }}
							</td>
							<td>
								{{ historyItem.duration }}
							</td>
							<td> 
								<div class="btn btn-secondary" v-on:click="show(historyItem)"> Show data </div>
							</td>
						</tr>
					</tbody>
			</table>
			</div>

			<div class="col-md-8">
				<d3-tree-view ref="treeView" :data="visibleData" v-on:node-clicked="paginateChildren" style="width:2500px; height:1000px"></d3-tree-view>
			</div>
			
		</div>
	</div>
</template>

<script>

export default {
	name: 'queryViewer',
	props: ['query', 'deleteFormId'], 
	data: () => {
		return {
			visibleData: {text:'result', children: []}
		}
	},
	methods: {
		isScalar (data) {
			return typeof(data) !== 'object' && !Array.isArray(data);
		},
		convertToTree: function(data) {
			
			if (this.isScalar(data) ) {
				console.log('Scalar: ', data);
				throw(data, " is a scalar value friendo");
				return;
			}

			let attributes = Object.keys(data);
			let node = {position:'left', color: 'lightsteelblue', selected: false};
			node.children = []; 

			for(let attributeName of attributes) {
				let child = null;
				if (this.isScalar(data[attributeName])) {
					child = { text: attributeName + ": " + data[attributeName], color: 'yellow', position: 'right'};

				} else if (data[attributeName] === null) { 
					child = { text: attributeName + ": null", position: 'right', color: 'white', selected: false}	
				} else {
					child = this.convertToTree(data[attributeName]);
					child.text = attributeName;

					if(child.children.length == 0 ){
						child.text += ' (empty)';	
						child.position = 'left';
						child.color = 'white';
					}
				}

				node.children.push(child);
			}
		

			return node;
		},
		paginate: function(tree, paginationSize) {
			// base case
			if (!tree.children || tree.children.length ==0) {
				return tree;
			}

			// case to not apply pagination
			if(tree.children.length <= paginationSize) {
				tree.children.forEach( child => {
					this.paginate(child, paginationSize);
				});
				return tree;
			}


			// case to apply pagination
			tree.pages = [];
			let totalChildrenCount = tree.children.length;
			while (tree.children.length > 0) {
				tree.pages.push(tree.children.splice(0, paginationSize));	
			}

			tree.pages.forEach( (pageOfChildren, pageIndex) => {
				pageOfChildren.forEach( child => {
					child.parent = tree;
					this.paginate(child, paginationSize);
				});

				let previousPageIndex = (pageIndex - 1) < 0 ? tree.pages.length - 1 : pageIndex - 1;
				let nextPageIndex = (pageIndex + 1) % tree.pages.length;
				pageOfChildren.unshift({
					text: 'view page ' + previousPageIndex,
					color: 'red',
					changeIndex: previousPageIndex,
					position: 'right',
					role: 'paginatePrevious'
				});
			
				pageOfChildren.push({
					text: 'view page ' + nextPageIndex,
					color: 'red',
					changeIndex: nextPageIndex,
					position: 'right',
					role: 'paginateNext'	
				});
			});	
		
			// last, but not least, set the children of this tree to the first page
			tree.children = tree.pages[0];
			return tree;
		},
		show: function(historyItem) {
			let data = JSON.parse(historyItem.data);
			this.visibleData = this.paginate(this.convertToTree(data), 10);
			this.visibleData.text = historyItem.created_at;
			this.visibleData.position = 'left';
			this.visibleData.color = 'lightsteelblue';
			this.visibleData.selected = false;
		},
		deleteQuery () {
			document.getElementById(this.deleteFormId).submit();
		},
		submitQuery () {
			location.replace('/queries/' + this.query.id + '/submit')
		},
		editQuery () {
			location.replace('/queries/' + this.query.id + '/edit')
		},
		paginateChildren (childNode) {
			if( !childNode.role) {
				return;	
			}
			childNode.parent.children = childNode.parent.pages[childNode.changeIndex];
		}
	},
	computed: {
		history () {
			return this.query.history.sort((a,b) => {
				return new Date(b.created_at) - new Date(a.created_at);
			});
		}
	},
	mounted () {
		if(this.history.length) {
			this.show(this.history[0]);
		}
	}
}

</script>

<style scoped>

</style>
